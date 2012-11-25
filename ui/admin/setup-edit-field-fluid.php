<?php
$field = array_merge( $field_settings[ 'field_defaults' ], $field );

// fix 2.0 alpha bug
if ( 'permalink' == $field[ 'type' ] )
    $field[ 'type' ] = 'slug';

$no_advanced = array();

$field_type_options = array();

foreach ( $field_settings[ 'field_types' ] as $field_type => $field_label ) {
    $field_type_options[ $field_type ] = PodsForm::options_setup( $field_type );

    if ( empty( $field_type_options[ $field_type ] ) )
        $no_advanced[] = $field_type;
}

$pick_object = trim( pods_var( 'pick_object', $field ) . '-' . pods_var( 'pick_val', $field ), '-' );
?>
<tr id="row-<?php echo $pods_i; ?>" class="pods-manage-row pods-field-new pods-field-<?php echo esc_attr( pods_var( 'name', $field ) ) . ( '--1' === $pods_i ? ' flexible-row' : ' pods-submittable-fields' ); ?>" valign="top" data-row="<?php echo $pods_i; ?>">
    <th scope="row" class="check-field pods-manage-sort">
        <img src="<?php echo PODS_URL; ?>ui/images/handle.gif" alt="<?php esc_attr_e( 'Move', 'pods' ); ?>" />
    </th>
    <td class="pods-manage-row-label">
        <strong> <a class="pods-manage-row-edit row-label" title="<?php esc_attr_e( 'Edit this field', 'pods' ); ?>" href="#edit-field">
            <?php _e( 'New Field' ); ?>
        </a> <abbr title="required" class="required hidden">*</abbr> </strong>

        <div class="row-actions">
            <span class="edit">
                <a title="<?php esc_attr_e( 'Edit this field', 'pods' ); ?>" class="pods-manage-row-edit" href="#edit-field"><?php _e( 'Edit', 'pods' ); ?></a> |
            </span>
            <span class="pods-manage-row-delete">
                <a class="submitdelete" title="<?php esc_attr_e( 'Delete this field', 'pods' ); ?>" href="#delete-field"><?php _e( 'Delete', 'pods' ); ?></a>
            </span>
        </div>
        <div class="pods-manage-row-wrapper" id="pods-manage-field-<?php echo $pods_i; ?>">
            <input type="hidden" name="field_data_json[<?php echo $pods_i; ?>]" value="" class="field_data" />

            <div class="pods-manage-field pods-dependency">
                <div class="pods-tabbed">
                    <ul class="pods-tabs">
                        <li class="pods-tab"><a href="#pods-basic-options-<?php echo $pods_i; ?>" class="selected"><?php _e( 'Basic', 'pods' ); ?></a></li>
                        <li class="pods-tab pods-excludes-on pods-excludes-on-field-data-type pods-excludes-on-field-data-type-<?php echo implode( ' pods-excludes-on-field-data-type-', $no_advanced ); ?>"><a href="#pods-additional-field-options-<?php echo $pods_i; ?>"><?php _e( 'Additional Field Options', 'pods' ); ?></a></li>
                        <li class="pods-tab"><a href="#pods-advanced-options-<?php echo $pods_i; ?>"><?php _e( 'Advanced', 'pods' ); ?></a></li>
                    </ul>
                    <div class="pods-tab-group">
                        <div id="pods-basic-options-<?php echo $pods_i; ?>" class="pods-tab pods-basic-options">
                            <div class="pods-field-option">
                                <?php echo PodsForm::label( 'field_data[' . $pods_i . '][label]', __( 'Label', 'pods' ), __( 'help', 'pods' ) ); ?>
                                <?php echo PodsForm::field( 'field_data[' . $pods_i . '][label]', pods_var_raw( 'label', $field, '' ), 'text', array( 'class' => 'pods-validate pods-validate-required' ) ); ?>
                            </div>
                            <div class="pods-field-option">
                                <?php echo PodsForm::label( 'field_data[' . $pods_i . '][name]', __( 'Name', 'pods' ), __( 'You will use this name to programatically reference this field throughout WordPress', 'pods' ) ); ?>
                                <?php echo PodsForm::field( 'field_data[' . $pods_i . '][name]', pods_var_raw( 'name', $field, '' ), 'db', array( 'attributes' => array( 'maxlength' => 50, 'data-sluggable' => 'field_data[' . $pods_i . '][label]' ), 'class' => 'pods-validate pods-validate-required pods-slugged-lower' ) ); ?>
                            </div>
                            <div class="pods-field-option">
                                <?php echo PodsForm::label( 'field_data[' . $pods_i . '][description]', __( 'Description', 'pods' ), __( 'help', 'pods' ) ); ?>
                                <?php echo PodsForm::field( 'field_data[' . $pods_i . '][description]', pods_var_raw( 'description', $field, '' ), 'text' ); ?>
                            </div>
                            <div class="pods-field-option">
                                <?php echo PodsForm::label( 'field_data[' . $pods_i . '][type]', __( 'Field Type', 'pods' ), __( 'help', 'pods' ) ); ?>
                                <?php echo PodsForm::field( 'field_data[' . $pods_i . '][type]', pods_var_raw( 'type', $field, '' ), 'pick', array( 'data' => pods_var_raw( 'field_types_select', $field_settings ), 'class' => 'pods-dependent-toggle' ) ); ?>
                            </div>
                            <div class="pods-field-option-container pods-depends-on pods-depends-on-field-data-type pods-depends-on-field-data-type-pick">
                                <div class="pods-field-option">
                                    <?php echo PodsForm::label( 'field_data[' . $pods_i . '][pick_object]', __( 'Related To', 'pods' ), __( 'help', 'pods' ) ); ?>
                                    <?php echo PodsForm::field( 'field_data[' . $pods_i . '][pick_object]', $pick_object, 'pick', array( 'required' => true, 'data' => pods_var_raw( 'pick_object', $field_settings ), 'class' => 'pods-dependent-toggle' ) ); ?>
                                </div>
                                <div class="pods-field-option pods-depends-on pods-depends-on-field-data-pick-object pods-depends-on-field-data-pick-object-custom-simple">
                                    <?php echo PodsForm::label( 'field_data[' . $pods_i . '][pick_custom]', __( 'Custom Defined Options', 'pods' ), __( 'One option per line, use <em>value|Label</em> for separate values and labels' ) ); ?>
                                    <?php echo PodsForm::field( 'field_data[' . $pods_i . '][pick_custom]', pods_var_raw( 'pick_custom', $field, '' ), 'paragraph' ); ?>
                                </div>
                                <div class="pods-field-option pods-wildcard-on pods-wildcard-on-field-data-pick-object" data-wildcard="pod-" data-dependency-trigger="pods_sister_field">
                                    <?php echo PodsForm::label( 'field_data[' . $pods_i . '][sister_id]', __( 'Bi-directional Field', 'pods' ), __( 'Bi-directional fields will update their related field for any item you select. This feature is only available for two relationships between two Pods.<br /><br />For example, when you update a Parent pod item to relate to a Child item, when you go to edit that Child item you will see the Parent pod item selected.', 'pods' ) ); ?>

                                    <div class="pods-sister-field">
                                        <?php echo PodsForm::field( 'field_data[' . $pods_i . '][sister_id]', pods_var_raw( 'sister_id', $field, '' ), 'text' ); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="pods-field-option-group">
                                <p class="pods-field-option-group-label">
                                    <?php _e( 'Options', 'pods' ); ?>
                                </p>

                                <div class="pods-pick-values pods-pick-checkbox">
                                    <ul>
                                        <li>
                                            <?php echo PodsForm::field( 'field_data[' . $pods_i . '][required]', pods_var_raw( 'required', $field, 0 ), 'boolean', array( 'class' => 'pods-dependent-toggle', 'boolean_yes_label' => __( 'Required', 'pods' ), 'help' => __( 'help', 'pods' ) ) ); ?>
                                        </li>
                                        <li>
                                            <?php echo PodsForm::field( 'field_data[' . $pods_i . '][unique]', pods_var_raw( 'unique', $field, 0 ), 'boolean', array( 'class' => 'pods-dependent-toggle', 'boolean_yes_label' => __( 'Unique', 'pods' ), 'help' => __( 'help', 'pods' ) ) ); ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div id="pods-additional-field-options-<?php echo $pods_i; ?>" class="pods-tab pods-additional-field-options">
                            <?php
                                foreach ( $field_settings[ 'field_types' ] as $field_type => $field_label ) {
                            ?>
                                <div class="pods-depends-on pods-depends-on-field-data-type pods-depends-on-field-data-type-<?php echo PodsForm::clean( $field_type, true ); ?>">
                                    <?php
                                        $field_options = $field_type_options[ $field_type ];

                                        include PODS_DIR . 'ui/admin/field-option.php';
                                    ?>
                                </div>
                            <?php
                                }
                            ?>
                        </div>

                        <div id="pods-advanced-options-<?php echo $pods_i; ?>" class="pods-tab pods-advanced-options">
                            <?php
                                foreach ( $field_settings[ 'advanced_fields' ] as $group => $fields ) {
                            ?>
                                <h4><?php echo $group; ?></h4>
                            <?php
                                    $field_options = PodsForm::fields_setup( $fields );

                                    include PODS_DIR . 'ui/admin/field-option.php';
                                }
                            ?>

                            <?php if ( defined( 'PODS_DEVELOPER' ) && PODS_DEVELOPER ) { ?>
                                <h4><?php _e( 'Conditional Logic', 'pods' ); ?></h4>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="pods-manage-row-actions submitbox">
                        <div class="pods-manage-row-delete">
                            <a class="submitdelete deletion" href="#delete-field"><?php _e( 'Delete Field', 'pods' ); ?></a>
                        </div>
                        <p class="pods-manage-row-save">
                            <a class="pods-manage-row-cancel" href="#cancel-edit-field"><?php _e( 'Cancel', 'pods' ); ?></a> &nbsp;&nbsp; <a href="#save-field" class="button-primary pods-button-update"><?php _e( 'Update Field', 'pods' ); ?></a><a href="#save-field" class="button-primary pods-button-add"><?php _e( 'Add Field', 'pods' ); ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </td>
    <td class="pods-manage-row-name">
        <a title="Edit this field" class="pods-manage-row-edit row-name" href="#edit-field"><?php echo esc_html( pods_var_raw( 'name', $field ) ); ?></a>
    </td>
    <td class="pods-manage-row-type">
        <?php
        $type = 'Unknown';

        if ( isset( $field_types[ pods_var( 'type', $field ) ] ) )
            $type = $field_types[ pods_var( 'type', $field ) ];

        echo esc_html( $type ) . ' <span class="pods-manage-row-more">[type: ' . pods_var( 'type', $field ) . ']</span>';

        if ( 'pick' == pods_var( 'type', $field ) && '' != pods_var( 'pick_object', $field, '' ) ) {
            $pick_object_name = null;

            foreach ( $field_settings[ 'pick_object' ] as $object => $object_label ) {
                if ( null !== $pick_object_name )
                    break;

                if ( '-- Select --' == $object_label )
                    continue;

                if ( is_array( $object_label ) ) {
                    foreach ( $object_label as $sub_object => $sub_object_label ) {
                        if ( $pick_object == $sub_object ) {
                            $object = rtrim( $object, 's' );

                            if ( false !== strpos( $object, 'ies' ) )
                                $object = str_replace( 'ies', 'y', $object );

                            $sub_object_label = preg_replace( '/(\s\([\w\d\s]*\))/', '', $sub_object_label );
                            $pick_object_name = esc_html( $sub_object_label ) . ' <small>(' . esc_html( $object ) . ')</small>';

                            break;
                        }
                    }
                }
                elseif ( pods_var( 'pick_object', $field ) == $object ) {
                    $pick_object_name = $object_label;

                    break;
                }
            }

            if ( null === $pick_object_name ) {
                $pick_object_name = ucwords( str_replace( array( '-', '_' ), ' ', pods_var_raw( 'pick_object', $field ) ) );

                if ( 0 < strlen( pods_var_raw( 'pick_val', $field ) ) )
                    $pick_object_name = pods_var_raw( 'pick_val', $field ) . ' (' . $pick_object_name . ')';
            }
            ?>
            <br /><span class="pods-manage-field-type-desc">&rsaquo; <?php echo $pick_object_name; ?></span>
            <?php
        }
        ?>
    </td>
</tr>
