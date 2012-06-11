<?php
$field = array_merge($field_settings['field_defaults'], $field);
?>
        <tr id="row-<?php echo $i; ?>" class="pods-manage-row pods-field-<?php echo esc_attr(pods_var('name', $field)) . ( '--1' === $i ? ' flexible-row' : '' ); ?>" valign="top" data-row="<?php echo $i; ?>">
            <th scope="row" class="check-field pods-manage-sort">
                <img src="<?php echo PODS_URL; ?>/ui/images/handle.gif" alt="<?php esc_attr_e( 'Move', 'pods' ); ?>" />
            </th>
            <td class="pods-manage-row-label">
                <strong>
                    <a class="pods-manage-row-edit row-label" title="<?php esc_attr_e( 'Edit this field', 'pods' ); ?>" href="#edit-field">
                        <?php echo esc_html(pods_var('label', $field)); ?>
                    </a>
                    <abbr title="required" class="required<?php echo (1 == pods_var('required', $field) ? '' : ' hidden'); ?>">*</abbr>
                </strong>
<?php
if ('__1' != pods_var('id', $field)) {
?>
                <span class="pods-manage-row-more">
                    [id: <?php echo esc_html(pods_var('id', $field)); ?>]
                </span>
<?php
}
?>
                <div class="row-actions">
                    <span class="edit">
                        <a title="<?php esc_attr_e( 'Edit this field', 'pods' ); ?>" class="pods-manage-row-edit" href="#edit-field"><?php _e('Edit', 'pods'); ?></a> |
                    </span>
                    <span class="pods-manage-row-delete">
                        <a class="submitdelete" title="<?php esc_attr_e( 'Delete this field', 'pods' ); ?>" href="#delete-field"><?php _e('Delete', 'pods'); ?></a>
                    </span>
                </div>
                <div class="pods-manage-row-wrapper" id="pods-manage-field-<?php echo $i; ?>">
                    <?php if ('__1' != pods_var('id', $field)) { ?>
                        <input type="hidden" name="field_data[<?php echo $i; ?>][id]" value="<?php echo pods_var('id', $field); ?>" />
                    <?php } ?>

                    <div class="pods-manage-field pods-dependency">
                        <div class="pods-tabbed">
                            <ul class="pods-tabs">
                                <li class="pods-tab"><a href="#pods-basic-options-<?php echo $i; ?>" class="selected"><?php _e('Basic', 'pods'); ?></a></li>
                                <li class="pods-tab pods-excludes-on pods-excludes-on-field-data-type pods-excludes-on-field-data-type-slug"><a href="#pods-additional-field-options-<?php echo $i; ?>"><?php _e('Additional Field Options', 'pods'); ?></a></li>
                                <li class="pods-tab"><a href="#pods-advanced-options-<?php echo $i; ?>"><?php _e('Advanced', 'pods'); ?></a></li>
                            </ul>
                            <div class="pods-tab-group">
                                <div id="pods-basic-options-<?php echo $i; ?>" class="pods-tab pods-basic-options">
                                    <div class="pods-field-option">
                                        <?php echo PodsForm::label('field_data[' . $i . '][name]', __('Name', 'pods'), __('help', 'pods')); ?>
                                        <?php echo PodsForm::field('field_data[' . $i . '][name]', pods_var('name', $field), 'db'); ?>
                                    </div>
                                    <div class="pods-field-option">
                                        <?php echo PodsForm::label('field_data[' . $i . '][label]', __('Label', 'pods'), __('help', 'pods')); ?>
                                        <?php echo PodsForm::field('field_data[' . $i . '][label]', pods_var('label', $field), 'text'); ?>
                                    </div>
                                    <div class="pods-field-option">
                                        <?php echo PodsForm::label('field_data[' . $i . '][description]', __('Description', 'pods'), __('help', 'pods')); ?>
                                        <?php echo PodsForm::field('field_data[' . $i . '][description]', pods_var('description', $field), 'text'); ?>
                                    </div>
                                    <div class="pods-field-option">
                                        <?php echo PodsForm::label('field_data[' . $i . '][type]', __('Field Type', 'pods'), __('help', 'pods')); ?>
                                        <?php echo PodsForm::field('field_data[' . $i . '][type]', pods_var('type', $field), 'pick', array('data' => pods_var('field_types', $field_settings), 'class' => 'pods-dependent-toggle')); ?>
                                    </div>
                                    <div class="pods-field-option-container pods-depends-on pods-depends-on-field-data-type pods-depends-on-field-data-type-pick">
                                        <div class="pods-field-option">
                                            <?php echo PodsForm::label('field_data[' . $i . '][pick_object]', __('Related To', 'pods'), __('help', 'pods')); ?>
                                            <?php echo PodsForm::field('field_data[' . $i . '][pick_object]', pods_var('pick_object', $field), 'pick', array('data' => pods_var('pick_object', $field_settings), 'class' => 'pods-dependent-toggle')); ?>
                                        </div>
                                        <div class="pods-field-option pods-depends-on pods-depends-on-field-data-pick-object pods-depends-on-field-data-pick-object-custom-simple">
                                            <?php echo PodsForm::label('field_data[' . $i . '][pick_custom]', __('Custom Defined Options', 'pods'), __('One option per line, use value|Label for separate values and labels')); ?>
                                            <?php echo PodsForm::field('field_data[' . $i . '][pick_custom]', pods_var('pick_custom', $field), 'textarea'); ?>
                                        </div>
                                        <div class="pods-field-option pods-depends-on pods-depends-on-field-data-pick-object pods-depends-on-field-data-pick-object-pod-event">
                                            <?php echo PodsForm::label('field_data[' . $i . '][sister_field_id]', __('Bi-Directional Related', 'pods'), __('help', 'pods')); ?>
                                            <?php echo PodsForm::field('field_data[' . $i . '][sister_field_id]', pods_var('sister_field_id', $field), 'pick', array('data' => pods_var('sister_field_id', $field_settings))); ?>
                                        </div>
                                    </div>
                                    <div class="pods-field-option-group">
                                        <p class="pods-field-option-group-label">
                                            <?php _e('Options', 'pods'); ?>
                                        </p>
                                        <div class="pods-field-option-group-values">
                                            <div class="pods-field-option-group-value">
                                                <?php echo PodsForm::field('field_data[' . $i . '][required]', pods_var('required', $field), 'boolean', array('class' => 'pods-dependent-toggle')); ?>
                                                <?php echo PodsForm::label('field_data[' . $i . '][required]', __('Required', 'pods'), __('help', 'pods')); ?>
                                            </div>
                                            <div class="pods-field-option-group-value pods-excludes-on pods-excludes-on-field-data-type pods-excludes-on-field-data-type-slug pods-excludes-on-field-data-type-pick pods-excludes-on-field-data-type-file pods-excludes-on-field-data-type-boolean">
                                                <?php echo PodsForm::field('field_data[' . $i . '][unique]', pods_var('unique', $field), 'boolean', array('class' => 'pods-dependent-toggle')); ?>
                                                <?php echo PodsForm::label('field_data[' . $i . '][unique]', __('Unique', 'pods'), __('help', 'pods')); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="pods-additional-field-options-<?php echo $i; ?>" class="pods-tab pods-additional-field-options">
                                    <?php
                                        foreach ( $field_settings[ 'field_types' ] as $field_type => $field_label ) {
                                            $depends_on = false;
                                    ?>
                                        <div class="pods-depends-on pods-depends-on-field-data-type pods-depends-on-field-data-type-<?php echo PodsForm::clean( $field_type, true ); ?>">
                                            <?php
                                                $field_options = PodsForm::options_setup( $field_type );

                                                foreach ( $field_options as $field_name => $field_option ) {
                                                    $field_option = (array) $field_option;

                                                    $depends = PodsForm::dependencies( $field_option, 'field-data-' );

                                                    if ( false !== $depends_on && $depends_on != $depends ) {
                                            ?>
                                                </div>
                                            <?php
                                                    }
                                                    if ( $depends_on != $depends ) {
                                                        if ( !empty( $depends ) ) {
                                            ?>
                                                <div class="pods-field-option-container <?php echo $depends; ?>">
                                            <?php
                                                        }
                                                    }

                                                    if ( !is_array( $field_option[ 'group' ] ) ) {
                                            ?>
                                                    <div class="pods-field-option">
                                                        <?php echo PodsForm::row( 'field_data[' . $i . '][' . $field_name . ']', pods_var( $field_name, $field, $field_option[ 'default' ] ), $field_option[ 'type' ], $field_option ); ?>
                                                    </div>
                                            <?php
                                                    }
                                                    else {
                                            ?>
                                                    <div class="pods-field-option-group">
                                                        <p class="pods-field-option-group-label">
                                                            <?php echo $field_option[ 'label' ]; ?>
                                                        </p>

                                                        <div class="pods-field-option-group-values">
                                                            <?php
                                                                foreach ( $field_option[ 'group' ] as $field_group_name => $field_group_option ) {
                                                                    $field_group_option = (array) $field_group_option;

                                                                    if ( 'boolean' != $field_group_option[ 'type' ] )
                                                                        continue;

                                                                    $field_group_option[ 'grouped' ] = 1;

                                                                    $depends_option = PodsForm::dependencies( $field_group_option, 'field-data-' );
                                                            ?>
                                                                <div class="pods-field-option-group-value <?php echo $depends_option; ?>">
                                                                    <?php echo PodsForm::field( 'field_data[' . $i . '][' . $field_group_name . ']', pods_var( $field_group_name, $field, $field_group_option[ 'default' ] ), $field_group_option[ 'type' ], $field_group_option ); ?>
                                                                </div>
                                                            <?php
                                                                }
                                                            ?>
                                                        </div>
                                                    </div>
                                            <?php
                                                    }

                                                    if ( false !== $depends_on || !empty( $depends ) )
                                                        $depends_on = $depends;
                                                }

                                                if ( !empty( $depends_on ) ) {
                                            ?>
                                                </div>
                                            <?php
                                                }
                                            ?>
                                        </div>
                                    <?php
                                        }
                                    ?>
                                </div>

                                <div id="pods-advanced-options-<?php echo $i; ?>" class="pods-tab pods-advanced-options">
                                    <h4><?php _e('Visual', 'pods'); ?></h4>
                                    <div class="pods-field-option">
                                        <?php echo PodsForm::label('field_data[' . $i . '][css_class_name]', __('CSS Class Name', 'pods'), __('help', 'pods')); ?>
                                        <?php echo PodsForm::field('field_data[' . $i . '][css_class_name]', pods_var('css_class_name', $field), 'text'); ?>
                                    </div>
                                    <div class="pods-field-option">
                                        <?php echo PodsForm::label('field_data[' . $i . '][input_helper]', __('Input Helper', 'pods'), __('help', 'pods')); ?>
                                        <?php echo PodsForm::field('field_data[' . $i . '][input_helper]', pods_var('input_helper', $field), 'pick', array('data' => pods_var('input_helper', $field_settings))); ?>
                                    </div>
                                    <h4><?php _e('Values', 'pods'); ?></h4>
                                    <div class="pods-field-option">
                                        <?php echo PodsForm::label('field_data[' . $i . '][default_value]', __('Default Value', 'pods'), __('help', 'pods')); ?>
                                        <?php echo PodsForm::field('field_data[' . $i . '][default_value]', pods_var('default_value', $field), 'text'); ?>
                                    </div>
                                    <div class="pods-field-option">
                                        <?php echo PodsForm::label('field_data[' . $i . '][default_value_parameter]', __('Set Default Value via Parameter', 'pods'), __('help', 'pods')); ?>
                                        <?php echo PodsForm::field('field_data[' . $i . '][default_value_parameter]', pods_var('default_value_parameter', $field), 'text'); ?>
                                    </div>
                                    <h4><?php _e('Visibility', 'pods'); ?></h4>
                                        <div class="pods-field-option-group">
                                            <p class="pods-field-option-group-label">
                                                <?php _e('Restrict Access', 'pods'); ?>
                                            </p>
                                            <div class="pods-field-option-group-values">
                                                <div class="pods-field-option-group-value">
                                                    <?php echo PodsForm::field('field_data[' . $i . '][admin_only]', pods_var('admin_only', $field), 'boolean'); ?>
                                                    <?php echo PodsForm::label('field_data[' . $i . '][admin_only]', __('Show to Admins Only?', 'pods'), __('help', 'pods')); ?>
                                                </div>
                                                <div class="pods-field-option-group-value">
                                                    <?php echo PodsForm::field('field_data[' . $i . '][restrict_capability]', pods_var('restrict_capability', $field), 'boolean', array('class' => 'pods-dependent-toggle')); ?>
                                                    <?php echo PodsForm::label('field_data[' . $i . '][restrict_capability]', __('Restrict access by Capability?', 'pods'), __('help', 'pods')); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <div class="pods-depends-on pods-depends-on-field-data-restrict-capability">
                                        <div class="pods-field-option">
                                            <?php echo PodsForm::label('field_data[' . $i . '][capability_allowed]', __('Capability Allowed', 'pods'), __('help', 'pods')); ?>
                                            <?php echo PodsForm::field('field_data[' . $i . '][capability_allowed]', pods_var('capability_allowed', $field), 'text'); ?>
                                        </div>
                                    </div>
                                    <h4><?php _e('Validation', 'pods'); ?></h4>
                                    <div class="pods-field-option">
                                        <?php echo PodsForm::label('field_data[' . $i . '][regex_validation]', __('RegEx Validation', 'pods'), __('help', 'pods')); ?>
                                        <?php echo PodsForm::field('field_data[' . $i . '][regex_validation]', pods_var('regex_validation', $field), 'text'); ?>
                                    </div>
                                    <div class="pods-field-option">
                                        <?php echo PodsForm::label('field_data[' . $i . '][message_regex]', __("Message if field doesn't pass RegEx"), __('help', 'pods')); ?>
                                        <?php echo PodsForm::field('field_data[' . $i . '][message_regex]', pods_var('message_regex', $field), 'text'); ?>
                                    </div>
                                    <div class="pods-depends-on pods-depends-on-field-data-required">
                                        <div class="pods-field-option">
                                            <?php echo PodsForm::label('field_data[' . $i . '][message_required]', __('Message if field is blank', 'pods'), __('help', 'pods')); ?>
                                            <?php echo PodsForm::field('field_data[' . $i . '][message_required]', pods_var('message_required', $field), 'text'); ?>
                                        </div>
                                    </div>
                                    <div class="pods-depends-on pods-depends-on-field-data-unique">
                                        <div class="pods-field-option">
                                            <?php echo PodsForm::label('field_data[' . $i . '][message_unique]', __('Message if field is not unique', 'pods'), __('help', 'pods')); ?>
                                            <?php echo PodsForm::field('field_data[' . $i . '][message_unique]', pods_var('message_required', $field), 'text'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="pods-manage-row-actions submitbox">
                                <div class="pods-manage-row-delete">
                                    <a class="submitdelete deletion" href="#delete-field"><?php _e('Delete Field', 'pods'); ?></a>
                                </div>
                                <p class="pods-manage-row-save">
                                    <a class="pods-manage-row-cancel" href="#cancel-edit-field"><?php _e('Cancel', 'pods'); ?></a> &nbsp;&nbsp;
                                    <a href="#save-field" class="button-primary"><?php _e('Update Field', 'pods'); ?></a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
            <td class="pods-manage-row-name">
                <a title="Edit this field" class="pods-manage-row-edit row-name" href="#edit-field"><?php echo esc_html(pods_var('name', $field)); ?></a>
            </td>
            <td class="pods-manage-row-type">
                <?php echo esc_html((isset($field_types[pods_var('type', $field)]) ? $field_types[pods_var('type', $field)] : 'Unknown')) . ' <span class="pods-manage-row-more">[type: ' . pods_var('type', $field) . ']</span>'; ?>
<?php
if ('pick' == pods_var('type', $field) && '' != pods_var('pick_object', $field, '')) {
    $pick_object = null;
    foreach ($field_settings['pick_object'] as $object => $object_label) {
        if (null !== $pick_object)
            break;
        if ('-- Select --' == $object_label)
            continue;
        if (is_array($object_label)) {
            foreach ($object_label as $sub_object => $sub_object_label) {
                if (pods_var('pick_object', $field) == $sub_object) {
                    $object = rtrim($object, 's');
                    if (false !== strpos($object, 'ies'))
                        $object = str_replace('ies', 'y', $object);
                    $sub_object_label = preg_replace('/(\s\([\w\d\s]*\))/', '', $sub_object_label);
                    $pick_object = esc_html($sub_object_label) . ' <small>(' . esc_html($object) . ')</small>';
                    break;
                }
            }
        }
        elseif (pods_var('pick_object', $field) == $object) {
            $pick_object = $object_label;
            break;
        }
    }
    if (null === $pick_object)
        $pick_object = pods_var('pick_object', $field);
?>
                <br /><span class="pods-manage-field-type-desc">&rsaquo; <?php echo $pick_object; ?></span>
<?php
}
?>
            </td>
        </tr>