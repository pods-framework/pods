<?php
    $depends_on = false;

    foreach ( $field_options as $field_name => $field_option ) {
        $field_option = (array) $field_option;

        $depends = PodsForm::dependencies( $field_option, ( !isset( $advanced_options ) ? 'field-data-' : '' ) );

        if ( ( !empty( $depends_on ) || !empty( $depends ) ) && $depends_on != $depends ) {
            if ( !empty( $depends_on ) ) {
?>
    </div>
<?php
            }
            if ( !empty( $depends ) ) {
?>
    <div class="pods-field-option-container <?php echo $depends; ?>">
<?php
            }
        }

        if ( !is_array( $field_option[ 'group' ] ) ) {
            $row_name = $field_name;

            if ( !isset( $advanced_options ) )
                $row_name = 'field_data[' . $i . '][' . $field_name . ']';
?>
        <div class="pods-field-option">
            <?php echo PodsForm::row( $row_name, pods_var( $field_name, $field, $field_option[ 'default' ] ), $field_option[ 'type' ], $field_option ); ?>
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

                        $field_group_option[ 'boolean_yes_label' ] = $field_group_option[ 'label' ];

                        $depends_option = PodsForm::dependencies( $field_group_option, ( !isset( $advanced_options ) ? 'field-data-' : '' ) );

                        $row_name = $field_group_name;

                        if ( !isset( $advanced_options ) )
                            $row_name = 'field_data[' . $i . '][' . $field_group_name . ']';
                ?>
                    <div class="pods-field-option-group-value <?php echo $depends_option; ?>">
                        <?php echo PodsForm::field( $row_name, pods_var( $field_group_name, $field, $field_group_option[ 'default' ] ), $field_group_option[ 'type' ], $field_group_option ); ?>
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