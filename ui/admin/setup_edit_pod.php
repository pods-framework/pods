<?php
global $i;

$field_types = array('date' => 'Date / Time',
                     'number' => 'Number',
                     'boolean' => 'Yes / No',
                     'text' => 'Text',
                     'paragraph' => 'Paragraph Text',
                     'file' => 'File Upload',
                     'permalink' => 'Permalink (url-friendly)',
                     'pick' => 'Relationship');

$field_defaults = array('name' => 'new_field',
                        'label' => 'New Field',
                        'description' => '',
                        'type' => 'text',
                        'pick_object' => '',
                        'sister_field_id' => '',
                        'required' => 0,
                        'unique' => 0,
                        'date_format_type' => 'datetime',
                        'date_format_date' => 'mdy',
                        'date_format_time' => 'h_mma',
                        'date_format_type' => '12',
                        'text_format_type' => 'plain',
                        'text_format_website' => 'normal',
                        'text_format_phone' => '999-999-9999 x999',
                        'text_enable_phone_extension' => 0,
                        'text_max_length' => 255,
                        'text_size' => 'medium',
                        'text_allow_html' => 1,
                        'text_allowed_html_tags' => 'strong em a ul ol li b i',
                        'paragraph_editor' => 'tinymce',
                        'paragraph_allow_html' => 1,
                        'paragraph_allow_markdown' => 0,
                        'paragraph_allowed_html_tags' => 'strong em a ul ol li b i',
                        'number_format_type' => 'plain',
                        'number_format_currency_sign' => 'usd',
                        'number_format_currency_placement' => 'before',
                        'number_format' => '9,999.99',
                        'number_decimals' => 0,
                        'number_max_length' => 255,
                        'number_size' => 'medium',
                        'file_type' => 'single',
                        'file_uploader' => 'plupload',
                        'file_limit' => 5,
                        'file_restrict_filesize' => '10MB',
                        'file_restrict_filetypes' => '',
                        'boolean_format_type' => 'checkbox',
                        'boolean_yes_label' => 'Yes',
                        'boolean_no_label' => 'No',
                        'permalink_behavior' => '',
                        'pick_type' => 'single',
                        'pick_format_single' => 'dropdown',
                        'pick_format_multi' => 'checkbox',
                        'pick_limit' => 5,
                        'pick_filter' => '',
                        'pick_orderby' => '',
                        'pick_groupby' => '',
                        'pick_size' => 'medium',
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
                        'message_unique' => '');

$pick_object = array('' => '-- Select --',
                     'Custom' => array('custom-simple' => 'Simple (custom defined list)'),
                     'Pods' => array(),
                     'Post Types' => array(),
                     'Taxonomies' => array(),
                     'Other WP Objects' => array('user' => 'Users',
                                                 'comment' => 'Comments'));

$_pods = (array) $this->data->select(array('table' => '@wp_pods',
                                           'where' => '`type` = "pod"',
                                           'orderby' => '`name`',
                                           'limit' => -1,
                                           'search' => false,
                                           'pagination' => false));
foreach ($_pods as $pod) {
    if (!empty($pod->options))
        $pod->options = (object) json_decode($pod->options);
    $label = $pod->name;
    if ('' != pods_var('label', $pod->options, ''))
        $label = pods_var('label', $pod->options, '');
    $pick_object['Pods']['pod-' . $pod->name] = $label;
}

$post_types = get_post_types();
$ignore = array('attachment', 'revision', 'nav_menu_item');
foreach ($post_types as $post_type => $label) {
    if (in_array($post_type, $ignore) || empty($post_type))
        continue;
    $post_type = get_post_type_object($post_type);
    $pick_object['Post Types']['post-type-' . $post_type->name] = $post_type->label;
}

$taxonomies = get_taxonomies();
$ignore = array('nav_menu', 'link_category', 'post_format');
foreach ($taxonomies as $taxonomy => $label) {
    if (in_array($taxonomy, $ignore) || empty($taxonomy))
        continue;
    $taxonomy = get_taxonomy($taxonomy);
    $pick_object['Taxonomies']['taxonomy-' . $taxonomy->name] = $taxonomy->label;
}

$field_settings = array('field_types' => $field_types,
                        'field_defaults' => $field_defaults,
                        'pick_object' => $pick_object,
                        'sister_field_id' => array('' => '-- Select --'),
                        'date_format_type' => array('date' => 'Date',
                                                    'datetime' => 'Date + Time',
                                                    'time' => 'Time'),
                        'date_format' => array('mdy' => 'mm/dd/yyyy',
                                               'dmy' => 'dd/mm/yyyy',
                                               'dmy_dash' => 'dd-mm-yyyy',
                                               'dmy_dot' => 'dd.mm.yyyy',
                                               'ymd_slash' => 'yyyy/mm/dd',
                                               'ymd_dash' => 'yyyy-mm-dd',
                                               'ymd_dot' => 'yyyy.mm.dd'),
                        'date_time_format' => array('h_mma' => '1:25 PM',
                                                    'hh_mma' => '01:25 PM',
                                                    'h_mm' => '1:25',
                                                    'hh_mm' => '01:25'),
                        'date_time_type' => array('12' => '12 hour',
                                                    '24' => '24 hour'),
                        'text_format_type' => array('plain' => 'Plain Text',
                                                    'email' => 'E-mail Address (example@mail.com)',
                                                    'website' => 'Website (http://www.example.com/)',
                                                    'phone' => 'Phone Number'),
                        'text_format_website' => array('normal' => 'http://example.com/',
                                                            'no-www' => 'http://example.com/ (remove www)',
                                                            'force-www' => 'http://www.example.com/ (force www if no sub-domain provided)',
                                                            'no-http' => 'example.com',
                                                            'no-http-no-www' => 'example.com (force removal of www)',
                                                            'no-http-force-www' => 'www.example.com (force www if no sub-domain provided)'),
                        'text_format_phone' => array('US' => array('999-999-9999 x999' => '123-456-7890 x123',
                                                                        '(999) 999-9999 x999' => '(123) 456-7890 x123',
                                                                        '999.999.9999 x999' => '123.456.7890 x123'),
                                                          'International' => array('+9 999-999-9999 x999' => '+1 123-456-7890 x123',
                                                                                   '+9 (999) 999-9999 x999' => '+1 (123) 456-7890 x123',
                                                                                   '+9 999.999.9999 x999' => '+1 123.456.7890 x123')),
                        'text_size' => array('small' => 'Small',
                                             'medium' => 'Medium',
                                             'large' => 'Large'),
                        'paragraph_editor' => array('plain' => 'Plain Text Area',
                                                    'WYSIWYG' => array('tinymce' => 'TinyMCE (WP Default)',
                                                                       'cleditor' => 'CLEditor')),
                        'number_format_type' => array('plain' => 'Plain Number',
                                                      'currency' => 'Currency'),
                        'number_format_currency_sign' => array('usd' => '$ (USD)',
                                                               'cad' => '$ (CAD)'),
                        'number_format_currency_placement' => array('before' => 'Before ($100)',
                                                                    'after' => 'After (100$)',
                                                                    'none' => 'None (100)',
                                                                    'beforeaftercode' => 'Before with Currency Code after ($100 USD)'),
                        'number_format' => array('9,999.99' => '1,234.00',
                                                 '9999.99' => '1234.00',
                                                 '9.999,99' => '1.234,00',
                                                 '9999,99' => '1234,00'),
                        'number_size' => array('small' => 'Small',
                                               'medium' => 'Medium',
                                               'large' => 'Large'),
                        'file_type' => array('single' => 'Single File Upload',
                                             'multi-limited' => 'Multiple File Upload (limited uploads)',
                                             'multi-unlimited' => 'Multiple File Upload (no limit)'),
                        'file_uploader' => array('plupload' => 'Plupload (WP Default)',
                                                 'swfupload' => 'SWFUpload',
                                                 'basic' => 'HTML Upload (basic)'),
                        'boolean_format_type' => array('checkbox' => 'Checkbox',
                                                       'radio' => 'Radio Buttons',
                                                       'dropdown' => 'Drop Down'),
                        'permalink_behavior' => array('' => '-- Select --'),
                        'pick_type' => array('single' => 'Single Select',
                                             'multi-limited' => 'Multi Select (limited selections)',
                                             'multi-unlimited' => 'Multi Select (no limit)'),
                        'pick_format_single' => array('dropdown' => 'Drop Down',
                                                      'radio' => 'Radio Buttons',
                                                      'autocomplete' => 'Autocomplete'),
                        'pick_format_multi' => array('checkbox' => 'Checkboxes',
                                                     'multiselect' => 'Multi Select',
                                                     'autocomplete' => 'Autocomplete'),
                        'pick_size' => array('small' => 'Small',
                                             'medium' => 'Medium',
                                             'large' => 'Large'),
                        'input_helper' => array('' => '-- Select --'));

$pod = $this->api->load_pod((isset($obj->row['name']) ? $obj->row['name'] : false));
$pod['options'] = (array) $pod['options'];
foreach ($pod['options'] as $_option => $_value) {
    $pod[$_option] = $_value;
}

foreach ( $pod['fields'] as $_field => $_data) {
    $_data['options'] = (array) $_data['options'];
    foreach ($_data['options'] as $_option => $_value) {
        $pod['fields'][$_field][$_option] = $_value;
    }
}

$field_defaults = apply_filters('pods_field_defaults', apply_filters('pods_field_defaults_' . $pod['name'], $field_defaults, $pod));
$field_settings = apply_filters('pods_field_settings', apply_filters('pods_field_settings_' . $pod['name'], $field_settings, $pod));
$pod['fields'] = apply_filters('pods_fields_edit', apply_filters('pods_fields_edit_' . $pod['name'], $pod['fields'], $pod));

global $wpdb;
$max_length_name = 64;
$max_length_name -= 10; // Allow for WP Multisite or prefix changes in the future
$max_length_name -= strlen($wpdb->prefix . 'pods_tbl_');
?>
<div class="wrap pods-admin">
    <div id="icon-pods" class="icon32"><br /></div>
    <form action="" method="post">
        <input type="hidden" name="action" value="pods_admin" />
        <input type="hidden" name="method" value="save_pod" />
        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('pods-save_pod'); ?>" />
        <input type="hidden" name="id" value="<?php echo (int) $pod['id']; ?>" />
        <h2>
            Edit Pod:
            <span class="pods-sluggable">
                <span class="pods-slug">
                    <em><?php echo esc_html($pod['name']); ?></em>
                    <input type="button" class="edit-slug-button button" value="Edit" />
                </span>
                <span class="pods-slug-edit">
                    <?php echo PodsForm::field('name', pods_var('name', $pod), 'db', array('attributes' => array('maxlength' => $max_length_name, 'size' => 25), 'class' => 'pods-validate pods-validate-required')); ?>
                    <input type="button" class="save-button button" value="OK" /> <a class="cancel" href="#cancel-edit">Cancel</a>
                </span>
            </span>
        </h2>
        <div id="poststuff" class="has-right-sidebar meta-box-sortables">
            <img src="<?php echo PODS_URL; ?>/ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />
            <div id="side-info-field" class="inner-sidebar pods_floatmenu">
                <div id="side-sortables">
                    <div id="submitdiv" class="postbox pods-no-toggle">
                        <h3><span>Manage <small>(<a href="<?php echo $obj->var_update(array('action' . $obj->num => 'manage', 'id' . $obj->num => '')); ?>">&laquo; <?php _e('Back to Manage'); ?></a>)</small></span></h3>
                        <div class="inside">
                            <div class="submitbox" id="submitpost">
                                <div id="major-publishing-actions">
                                    <div id="delete-action">
                                        <a href="#delete-pod" class="submitdelete deletion pods-submittable" data-action="pods_admin" data-method="drop_pod" data-_wpnonce="<?php echo wp_create_nonce('pods-drop_pod'); ?>" data-name="<?php echo esc_attr(pods_var('name', $pod)); ?>">Delete Pod</a>
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
            <!-- /inner-sidebar -->
            <div id="post-body">
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
                                    <span>Label<?php pods_help(__("<h6>Label</h6>The label is the descriptive name to identify the Pod field.")); ?></span>
                                </th>
                                <th scope="col" id="machine-name" class="manage-column field-machine-name">
                                    <span>Name<?php pods_help(__("<h6>Name</h6>The name attribute is what is used to identify and access the Pod field programatically.")); ?></span>
                                </th>
                                <th scope="col" id="field-type" class="manage-column field-field-type">
                                    <span>Field Type<?php pods_help(__("<h6>Field Types</h6>Field types are used to determine what kind of data will be stored in the Pod.  They can range from, dates, text, files, etc.")); ?></span>
                                </th><!--
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
                                    <span>Label<?php pods_help(__("<h6>Label</h6>The label is the descriptive name to identify the Pod field.")); ?></span>
                                </th>
                                <th scope="col" id="machine-name" class="manage-column field-machine-name">
                                    <span>Name<?php pods_help(__("<h6>Name</h6>The name attribute is what is used to identify and access the Pod field programatically.")); ?></span>
                                </th>
                                <th scope="col" id="field-type" class="manage-column field-field-type">
                                    <span>Field Type<?php pods_help(__("<h6>Field Types</h6>Field types are used to determine what kind of data will be stored in the Pod.  They can range from, dates, text, files, etc.")); ?></span>
                                </th><!--
                                <th scope="col" id="comment" class="manage-column field-comment">
                                    <span>Comment</span>
                                </th>-->
                            </tr>
                        </tfoot>
                        <tbody class="pods-manage-list">
                            <?php
                                $i = 1;
                                foreach ($pod['fields'] as $field) {
                                    if ('_pods_empty' == $field['name'])
                                        continue;
                                    include PODS_DIR . 'ui/admin/setup_edit_pod_field.php';
                                    $i++;
                                }
                            ?>
                            <tr class="no-items<?php echo (1 < $i ? ' hidden' : ''); ?>">
                                <td class="colspanchange" colspan="4">No fields have been added yet</td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- /pods table -->
                    <p class="pods-manage-row-add">
                        <a href="#add-field" class="button-primary"><?php _e('Add Field'); ?></a>
                    </p>
                    <div id="pods-advanced" class="pods-toggled postbox closed">
                        <div class="handlediv" title="Click to toggle">
                            <br />
                        </div>
                        <h3><span>Advanced Options</span></h3>
                        <div class="inside pods-form">
                            <div class="pods-manage-field pods-dependency">
                                <div class="pods-tabbed">
                                    <ul class="pods-tabs">
<?php
if ('post_type' == pods_var('type', $pod)) {
?>
                                        <li class="pods-tab"><a href="#pods-advanced-post-type-labels">Post Type Labels</a></li>
                                        <li class="pods-tab"><a href="#pods-advanced-post-type-options">Post Type Options</a></li>
<?php
}
elseif ('taxonomy' == pods_var('type', $pod)) {
?>
                                        <li class="pods-tab"><a href="#pods-advanced-taxonomy-labels">Taxonomy Labels</a></li>
                                        <li class="pods-tab"><a href="#pods-advanced-taxonomy-options">Taxonomy Options</a></li>
<?php
}
elseif ('pod' == pods_var('type', $pod)) {
?>
                                        <li class="pods-tab"><a href="#pods-advanced-labels">Labels</a></li>
<?php
}
?>
                                        <li class="pods-tab"><a href="#pods-advanced-options">Options</a></li>
                                    </ul>

                                    <div class="pods-tab-group">
<?php
if ('post_type' == pods_var('type', $pod)) {
?>
                                        <div id="pods-advanced-post-type-labels" class="pods-tab">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_label', __('Label', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_label', pods_var('cpt_label', $pod, ucwords(str_replace('_', ' ', pods_var('name', $pod)))), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_singular_label', __('Singular Label', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_singular_label', pods_var('cpt_singular_label', $pod, pods_var('cpt_label', $pod, ucwords(str_replace('_', ' ', pods_var('name', $pod))))), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_add_new', __('Add New', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_add_new', pods_var('cpt_add_new', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_add_new_item', __('Add New <span class="pods-slugged">Item</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_add_new_item', pods_var('cpt_add_new_item', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_new_item', __('New <span class="pods-slugged">Item</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_new_item', pods_var('cpt_new_item', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_edit', __('Edit', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_edit', pods_var('cpt_edit', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_edit_item', __('Edit <span class="pods-slugged">Item</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_edit_item', pods_var('cpt_edit_item', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_view', __('View', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_view', pods_var('cpt_view', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_view_item', __('View <span class="pods-slugged">Item</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_view_item', pods_var('cpt_view_item', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_all_items', __('All <span class="pods-slugged">Items</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_all_items', pods_var('cpt_all_items', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_search_items', __('Search <span class="pods-slugged">Items</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_search_items', pods_var('cpt_search_items', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_not_found', __('Not Found', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_not_found', pods_var('cpt_not_found', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_not_found_in_trash', __('Not Found in Trash', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_not_found_in_trash', pods_var('cpt_not_found_in_trash', $pod), 'text'); ?>
                                            </div>
                                        </div>
                                        <div id="pods-advanced-post-type-options" class="pods-tab">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_description', __('Post Type Description', 'pods'), __('A short descriptive summary of what the post type is.')); ?>
                                                <?php echo PodsForm::field('cpt_description', pods_var('cpt_description', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_public', __('Public', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_public', pods_var('cpt_public', $pod, false), 'boolean'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_publicly_queryable', __('Publicly Queryable', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_publicly_queryable', pods_var('cpt_publicly_queryable', $pod, pods_var('cpt_public', $pod, false)), 'boolean'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_exclude_from_search', __('Exclude from Search', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_exclude_from_search', pods_var('cpt_exclude_from_search', $pod, (false === pods_var('cpt_public', $pod, false) ? true : false)), 'boolean'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_show_ui', __('Show UI', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_show_ui', pods_var('cpt_show_ui', $pod, pods_var('cpt_public', $pod, false)), 'boolean'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_show_in_menu', __('Show in Menu', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_show_in_menu', pods_var('cpt_show_in_menu', $pod), 'boolean', array('class' => 'pods-dependent-toggle')); ?>
                                            </div>
                                            <div class="pods-field-option-container pods-depends-on pods-depends-on-cpt-show-in-menu">
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('cpt_menu_name', __('Menu Name', 'pods'), __('help', 'pods')); ?>
                                                    <?php echo PodsForm::field('cpt_menu_name', pods_var('cpt_menu_name', $pod), 'text'); ?>
                                                </div>
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('cpt_menu_position', __('Menu Position', 'pods'), __('<ul><li>5 - below Posts</li><li>10 - below Media</li><li>15 - below Links</li><li>20 - below Pages</li><li>25 - below comments</li><li>60 - below first separator</li><li>65 - below Plugins</li><li>70 - below Users</li><li>75 - below Tools</li><li>80 - below Settings</li><li>100 - below second separator</li></ul>')); ?>
                                                    <?php echo PodsForm::field('cpt_menu_position', pods_var('cpt_menu_position', $pod, 20), 'number'); ?>
                                                </div>
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('cpt_menu_icon', __('Menu Icon URL', 'pods'), __('help', 'pods')); ?>
                                                    <?php echo PodsForm::field('cpt_menu_icon', pods_var('cpt_menu_icon', $pod), 'text'); ?>
                                                </div>
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('cpt_menu_string', __('<strong>Label: </strong> Parent Page', 'pods'), __('A top level page like "tools.php" or "edit.php?post_type=page", for when you want to show a CPT under a different top-level menu apart from it\'s own')); ?>
                                                    <?php echo PodsForm::field('cpt_menu_string', pods_var('cpt_menu_string', $pod), 'text'); ?>
                                                </div>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_show_in_nav_menus', __('Show in Navigation Menus', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_show_in_nav_menus', pods_var('cpt_show_in_nav_menus', $pod), 'boolean', array('class' => 'pods-dependent-toggle')); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_has_archive', __('Has Archive', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_has_archive', pods_var('cpt_has_archive', $pod, false), 'boolean'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_capability_type', __('Capability Type', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_capability_type', pods_var('cpt_capability_type', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_hierarchical', __('Hierarchical', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_hierarchical', pods_var('cpt_hierarchical', $pod, false), 'boolean', array('class' => 'pods-dependent-toggle')); ?>
                                            </div>
                                            <div class="pods-field-option-container pods-depends-on pods-depends-on-cpt-hierarchical">
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('cpt_parent_item_colon', __('<strong>Label: </strong> Parent <span class="pods-slugged">Item</span>'), __('help', 'pods')); ?>
                                                    <?php echo PodsForm::field('cpt_parent_item_colon', pods_var('cpt_parent_item_colon', $pod), 'text'); ?>
                                                </div>
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('cpt_parent', __('<strong>Label: </strong> Parent', 'pods'), __('help', 'pods')); ?>
                                                    <?php echo PodsForm::field('cpt_parent', pods_var('cpt_parent', $pod), 'text'); ?>
                                                </div>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_rewrite', __('Rewrite', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_rewrite', pods_var('cpt_rewrite', $pod, true), 'boolean', array('class' => 'pods-dependent-toggle')); ?>
                                            </div>
                                            <div class="pods-field-option-container pods-depends-on pods-depends-on-cpt-rewrite">
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('cpt_rewrite_custom_slug', __('Custom Rewrite Slug', 'pods'), __('help', 'pods')); ?>
                                                    <?php echo PodsForm::field('cpt_rewrite_custom_slug', pods_var('cpt_rewrite_custom_slug', $pod), 'text'); ?>
                                                </div>
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('cpt_rewrite_with_front', __('Allow Front Prepend', 'pods'), __('Allows permalinks to be prepended with front base (example: if your permalink structure is /blog/, then your links will be: Checked->/news/, Unchecked->/blog/news/)')); ?>
                                                    <?php echo PodsForm::field('cpt_rewrite_with_front', pods_var('cpt_rewrite_with_front', $pod, true), 'boolean'); ?>
                                                </div>
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('cpt_rewrite_feeds', __('Feeds', 'pods'), __('help', 'pods')); ?>
                                                    <?php echo PodsForm::field('cpt_rewrite_feeds', pods_var('cpt_rewrite_feeds', $pod, pods_var('cpt_has_archive', $pod, false)), 'boolean'); ?>
                                                </div>
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('cpt_rewrite_pages', __('Pages', 'pods'), __('help', 'pods')); ?>
                                                    <?php echo PodsForm::field('cpt_rewrite_pages', pods_var('cpt_rewrite_pages', $pod, true), 'boolean'); ?>
                                                </div>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_query_var', __('Query Var', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_query_var', pods_var('cpt_query_var', $pod, true), 'boolean'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('cpt_can_export', __('Exportable', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('cpt_can_export', pods_var('cpt_can_export', $pod, true), 'boolean'); ?>
                                            </div>
                                            <div class="pods-field-option-group">
                                                <p class="pods-field-option-group-label">
                                                    <?php _e('Supports'); ?>
                                                </p>
                                                <div class="pods-field-option-group-values">
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('cpt_supports_title', pods_var('cpt_supports_title', $pod, true), 'boolean'); ?>
                                                        <?php echo PodsForm::label('cpt_supports_title', __('Title', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('cpt_supports_editor', pods_var('cpt_supports_editor', $pod, true), 'boolean'); ?>
                                                        <?php echo PodsForm::label('cpt_supports_editor', __('Editor', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('cpt_supports_author', pods_var('cpt_supports_author', $pod, false), 'boolean'); ?>
                                                        <?php echo PodsForm::label('cpt_supports_author', __('Author', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('cpt_supports_thumbnail', pods_var('cpt_supports_thumbnail', $pod, false), 'boolean'); ?>
                                                        <?php echo PodsForm::label('cpt_supports_thumbnail', __('Featured Image', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('cpt_supports_excerpt', pods_var('cpt_supports_excerpt', $pod, false), 'boolean'); ?>
                                                        <?php echo PodsForm::label('cpt_supports_excerpt', __('Excerpt', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('cpt_supports_trackbacks', pods_var('cpt_supports_trackbacks', $pod, false), 'boolean'); ?>
                                                        <?php echo PodsForm::label('cpt_supports_trackbacks', __('Trackbacks', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('cpt_supports_custom_fields', pods_var('cpt_supports_custom_fields', $pod, false), 'boolean'); ?>
                                                        <?php echo PodsForm::label('cpt_supports_custom_fields', __('Custom Fields', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('cpt_supports_comments', pods_var('cpt_supports_comments', $pod, false), 'boolean'); ?>
                                                        <?php echo PodsForm::label('cpt_supports_comments', __('Comments', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('cpt_supports_revisions', pods_var('cpt_supports_revisions', $pod, false), 'boolean'); ?>
                                                        <?php echo PodsForm::label('cpt_supports_revisions', __('Revisions', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('cpt_supports_page_attributes', pods_var('cpt_supports_page_attributes', $pod, false), 'boolean'); ?>
                                                        <?php echo PodsForm::label('cpt_supports_page_attributes', __('Page Attributes', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('cpt_supports_post_formats', pods_var('cpt_supports_post_formats', $pod, false), 'boolean'); ?>
                                                        <?php echo PodsForm::label('cpt_supports_post_formats', __('Post Formats', 'pods'), __('help', 'pods')); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pods-field-option-group">
                                                <p class="pods-field-option-group-label">
                                                    <?php _e('Built-in Taxonomies'); ?>
                                                </p>
                                                <div class="pods-field-option-group-values">
<?php
    foreach ((array) $field_settings['pick_object']['Taxonomies'] as $taxonomy => $label) {
        $taxonomy = str_replace('taxonomy-', '', $taxonomy);
?>
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('cpt_built_in_taxonomies_' . $taxonomy, pods_var('cpt_built_in_taxonomies_' . $taxonomy, $pod, false), 'boolean'); ?>
                                                        <?php echo PodsForm::label('cpt_built_in_taxonomies_' . $taxonomy, $label); ?>
                                                    </div>
<?php
    }
?>
                                                </div>
                                            </div>
                                        </div>
<?php
}
elseif ('taxonomy' == pods_var('type', $pod)) {
?>
                                        <div id="pods-advanced-taxonomy-labels" class="pods-tab">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_label', __('Label', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_label', pods_var('ct_label', $pod, ucwords(str_replace('_', ' ', pods_var('name', $pod)))), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_singular_label', __('Singular Label', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_singular_label', pods_var('ct_singular_label', $pod, pods_var('ct_label', $pod, ucwords(str_replace('_', ' ', pods_var('name', $pod))))), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_add_new_item', __('Add New <span class="pods-slugged">Item</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_add_new_item', pods_var('ct_add_new_item', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_edit_item', __('Edit <span class="pods-slugged">Item</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_edit_item', pods_var('ct_edit_item', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_update_item', __('Update <span class="pods-slugged">Item</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_update_item', pods_var('ct_update_item', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_new_item_name', __('New <span class="pods-slugged">Item</span> Name'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_new_item_name', pods_var('ct_new_item_name', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_all_items', __('All <span class="pods-slugged">Items</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_all_items', pods_var('ct_all_items', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_search_items', __('Search <span class="pods-slugged">Items</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_search_items', pods_var('ct_search_items', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_popular_items', __('Popular <span class="pods-slugged">Items</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_popular_items', pods_var('ct_popular_items', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_separate_items_with_commas', __('Separate <span class="pods-slugged-lower">items</span> with commas'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_separate_items_with_commas', pods_var('ct_separate_items_with_commas', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_add_or_remove_items', __('Add or remove <span class="pods-slugged-lower">items</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_add_or_remove_items', pods_var('ct_add_or_remove_items', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_choose_from_the_most_used', __('Choose from the most used', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_choose_from_the_most_used', pods_var('ct_choose_from_the_most_used', $pod), 'text'); ?>
                                            </div>
                                        </div>
                                        <div id="pods-advanced-taxonomy-options" class="pods-tab">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_public', __('Public', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_public', pods_var('ct_public', $pod, true), 'boolean'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_show_ui', __('Show UI', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_show_ui', pods_var('ct_show_ui', $pod, pods_var('ct_public', $pod, true)), 'boolean', array('class' => 'pods-dependent-toggle')); ?>
                                            </div>
                                            <div class="pods-field-option pods-depends-on pods-depends-on-ct-show-ui">
                                                <?php echo PodsForm::label('ct_menu_name', __('Menu Name', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_menu_name', pods_var('ct_menu_name', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_show_in_nav_menus', __('Show in Nav Menus', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_show_in_nav_menus', pods_var('ct_show_in_nav_menus', $pod, pods_var('ct_public', $pod, true)), 'boolean'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_show_tagcloud', __('Allow in Tagcloud Widget', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_show_tagcloud', pods_var('ct_show_tagcloud', $pod, pods_var('ct_show_ui', $pod, pods_var('ct_public', $pod, true))), 'boolean'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_hierarchical', __('Hierarchical', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_hierarchical', pods_var('ct_hierarchical', $pod, false), 'boolean', array('class' => 'pods-dependent-toggle')); ?>
                                            </div>
                                            <div class="pods-field-option-container pods-depends-on pods-depends-on-ct-hierarchical">
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('ct_parent_item_colon', __('<strong>Label: </strong> Parent <span class="pods-slugged">Item</span>'), __('help', 'pods')); ?>
                                                    <?php echo PodsForm::field('ct_parent_item_colon', pods_var('ct_parent_item_colon', $pod), 'text'); ?>
                                                </div>
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('ct_parent', __('<strong>Label: </strong> Parent', 'pods'), __('help', 'pods')); ?>
                                                    <?php echo PodsForm::field('ct_parent', pods_var('ct_parent', $pod), 'text'); ?>
                                                </div>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_rewrite', __('Rewrite', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_rewrite', pods_var('ct_rewrite', $pod, true), 'boolean', array('class' => 'pods-dependent-toggle')); ?>
                                            </div>
                                            <div class="pods-field-option-container pods-depends-on pods-depends-on-ct-rewrite">
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('ct_rewrite_custom_slug', __('Custom Rewrite Slug', 'pods'), __('help', 'pods')); ?>
                                                    <?php echo PodsForm::field('ct_rewrite_custom_slug', pods_var('ct_rewrite_custom_slug', $pod), 'text'); ?>
                                                </div>
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('ct_rewrite_with_front', __('Allow Front Prepend', 'pods'), __('Allows permalinks to be prepended with front base (example: if your permalink structure is /blog/, then your links will be: Checked->/news/, Unchecked->/blog/news/)')); ?>
                                                    <?php echo PodsForm::field('ct_rewrite_with_front', pods_var('ct_rewrite_with_front', $pod, true), 'boolean'); ?>
                                                </div>
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('ct_rewrite_hierarchical', __('Hierarchical Permalinks', 'pods'), __('help', 'pods')); ?>
                                                    <?php echo PodsForm::field('ct_rewrite_hierarchical', pods_var('ct_rewrite_hierarchical', $pod, true), 'boolean'); ?>
                                                </div>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('ct_query_var', __('Query Var', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('ct_query_var', pods_var('ct_query_var', $pod), 'boolean'); ?>
                                            </div>
                                            <div class="pods-field-option-group">
                                                <p class="pods-field-option-group-label">
                                                    <?php _e('Associated Post Types'); ?>
                                                </p>
                                                <div class="pods-field-option-group-values">
<?php
    foreach ((array) $field_settings['pick_object']['Post Types'] as $post_type => $label) {
        $post_type = str_replace('post-type-', '', $post_type);
?>
                                                    <div class="pods-field-option-group-value">
                                                        <?php echo PodsForm::field('ct_built_in_post_types_' . $post_type, pods_var('ct_built_in_post_types_' . $post_type, $pod, false), 'boolean'); ?>
                                                        <?php echo PodsForm::label('ct_built_in_post_types_' . $post_type, $label); ?>
                                                    </div>
<?php
    }
?>
                                                </div>
                                            </div>
                                        </div>
<?php
}
elseif ('pod' == pods_var('type', $pod)) {
?>
                                        <div id="pods-advanced-labels" class="pods-tab">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('label', __('Label', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('label', pods_var('label', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('singular_label', __('Singular Label', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('singular_label', pods_var('singular_label', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('add_new', __('Add New', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('add_new', pods_var('add_new', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('add_new_item', __('Add New <span class="pods-slugged">Item</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('add_new_item', pods_var('add_new_item', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('new_item', __('New <span class="pods-slugged">Item</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('new_item', pods_var('new_item', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('edit', __('Edit', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('edit', pods_var('edit', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('edit_item', __('Edit <span class="pods-slugged">Item</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('edit_item', pods_var('edit_item', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('update_item', __('Update <span class="pods-slugged">Item</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('update_item', pods_var('update_item', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('view', __('View', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('view', pods_var('view', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('view_item', __('View <span class="pods-slugged">Item</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('view_item', pods_var('view_item', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('all_items', __('All <span class="pods-slugged">Items</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('all_items', pods_var('all_items', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('search_items', __('Search <span class="pods-slugged">Items</span>'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('search_items', pods_var('search_items', $pod), 'text'); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('not_found', __('Not Found', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('not_found', pods_var('not_found', $pod), 'text'); ?>
                                            </div>
                                        </div>
<?php
}
?>
                                        <div id="pods-advanced-options" class="pods-tab">
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('detail_url', __('Detail Page URL', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('detail_url', pods_var('detail_url', $pod), 'text'); ?>
                                            </div>
<?php
if ('pod' == pods_var('type', $pod)) {
?>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('show_in_menu', __('Show in Menu', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('show_in_menu', pods_var('show_in_menu', $pod), 'boolean', array('class' => 'pods-dependent-toggle')); ?>
                                            </div>
                                            <div class="pods-field-option-container pods-depends-on pods-depends-on-show-in-menu">
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('menu_name', __('Menu Name', 'pods'), __('help', 'pods')); ?>
                                                    <?php echo PodsForm::field('menu_name', pods_var('menu_name', $pod), 'text'); ?>
                                                </div>
                                                <div class="pods-field-option">
                                                    <?php echo PodsForm::label('menu_icon', __('Menu Icon', 'pods'), __('help', 'pods')); ?>
                                                    <?php echo PodsForm::field('menu_icon', pods_var('menu_icon', $pod), 'text'); ?>
                                                </div>
                                            </div>
<?php
}
?>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('pre_save_helpers', __('Pre-Save Helper(s)', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('pre_save_helpers', pods_var('pre_save_helpers', $pod), 'pick', array('data' => array('' => '-- Select --'))); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('post_save_helpers', __('Post-Save Helper(s)', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('post_save_helpers', pods_var('post_save_helpers', $pod), 'pick', array('data' => array('' => '-- Select --'))); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('pre_delete_helpers', __('Pre-Delete Helper(s)', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('pre_delete_helpers', pods_var('pre_delete_helpers', $pod), 'pick', array('data' => array('' => '-- Select --'))); ?>
                                            </div>
                                            <div class="pods-field-option">
                                                <?php echo PodsForm::label('post_delete_helpers', __('Post-Delete Helper(s)', 'pods'), __('help', 'pods')); ?>
                                                <?php echo PodsForm::field('post_delete_helpers', pods_var('post_delete_helpers', $pod), 'pick', array('data' => array('' => '-- Select --'))); ?>
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
            </div>
            <!-- /#post-body -->
        </div>
        <!-- /poststuff -->
    </form>
</div>
<script type="text/javascript">
<?php
ob_start();
$i = '--1';
$field =  array('id' => '__1',
                'name' => 'new__1',
                'label' => 'New Field __1',
                'type' => 'text');
include PODS_DIR . 'ui/admin/setup_edit_pod_field.php';
$new_field_row = ob_get_clean();

$pods_field_types = array();
foreach ($field_settings['field_types'] as $field_type => $field_label) {
    $pods_field_types[] = "'" . esc_js($field_type) . "' : '" . esc_js($field_label) . "'";
}
$pods_pick_objects = array();
foreach ($field_settings['pick_object'] as $object => $object_label) {
    if ('-- Select --' == $object_label)
        continue;
    if (is_array($object_label)) {
        $object = rtrim($object, 's');
        if (false !== strpos($object, 'ies'))
            $object = str_replace('ies', 'y', $object);
        foreach ($object_label as $sub_object => $sub_object_label) {
            $sub_object_label = preg_replace('/(\s\([\w\d\s]*\))/', '', $sub_object_label);
            $pods_pick_objects[] = "'" . esc_js($sub_object) . "' : '" . esc_js($sub_object_label) . " <small>(" . esc_js($object) . ")</small>'";
        }
    }
    else
        $pods_pick_objects[] = "'" . esc_js($object) . "' : '" . esc_js($object_label) . "'";
}
?>
    var pods_flexible_row = '<?php echo str_replace('</script>', '<\' + \'/script>', addslashes(trim(str_replace(array("\n", "\r", '        ', '    ', '  ', '  '), ' ', $new_field_row)))); ?>';
    var pods_field_types = {
        <?php echo implode(",\n        ", $pods_field_types); ?>
    };
    var pods_pick_objects = {
        <?php echo implode(",\n        ", $pods_pick_objects); ?>
    };
    function pods_admin_submittable_callback() {
        document.location = '<?php echo $obj->var_update(array('action' . $obj->num => 'manage', 'id' . $obj->num => '')); ?>';
    }
jQuery(function($){
    $(document).PodsAdmin('validate');
    $(document).PodsAdmin('submit');
    $(document).PodsAdmin('sluggable');
    $(document).PodsAdmin('sortable');
    $(document).PodsAdmin('collapsible');
    $(document).PodsAdmin('toggled');
    $(document).PodsAdmin('tabbed');
    $(document).PodsAdmin('dependency');
    $(document).PodsAdmin('flexible', ('undefined' != typeof pods_flexible_row ? pods_flexible_row : null));
});
</script>