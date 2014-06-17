<?php
$depends_on = false;

foreach ( $field_options as $field_name => $field_option ) {
    if ( false !== strpos( $field_name, 'helper' ) && !class_exists( 'Pods_Helpers' ) )
        continue;
    elseif ( $field_option[ 'developer_mode' ] && !pods_developer() )
        continue;

    $field_option = (array) $field_option;

    $depends = PodsForm::dependencies( $field_option, ( !isset( $pods_tab_form ) ? 'field-data-' : '' ) );

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

        if ( !isset( $pods_tab_form ) )
            $row_name = 'field_data[' . $pods_i . '][' . $field_name . ']';

        $value = $field_option[ 'default' ];

        if ( isset( $field_option[ 'value' ] ) && 0 < strlen( $field_option[ 'value' ] ) )
            $value = $field_option[ 'value' ];
        else
            $value = pods_var_raw( $field_name, $field, $value );

        if ( in_array( $field_option[ 'type' ], PodsForm::file_field_types() ) ) {
            if ( is_array( $value ) && !isset( $value[ 'id' ] ) ) {
                foreach ( $value as $k => $v ) {
                    if ( isset( $v[ 'id' ] ) )
                        $value[ $k ] = $v[ 'id' ];
                }
            }
        }
        ?>
        <div class="pods-field-option">
            <?php echo PodsForm::row( $row_name, $value, $field_option[ 'type' ], $field_option ); ?>
        </div>
        <?php
    }
    else {
        ?>
        <div class="pods-field-option-group">
            <p class="pods-field-option-group-label">
                <?php echo $field_option[ 'label' ]; ?>
            </p>

            <div class="pods-pick-values pods-pick-checkbox">
                <ul>
                    <?php
                    foreach ( $field_option[ 'group' ] as $field_group_name => $field_group_option ) {
                        $field_group_option = (array) $field_group_option;

                        if ( 'boolean' != $field_group_option[ 'type' ] )
                            continue;

                        $field_group_option[ 'boolean_yes_label' ] = $field_group_option[ 'label' ];

                        $depends_option = PodsForm::dependencies( $field_group_option, ( !isset( $pods_tab_form ) ? 'field-data-' : '' ) );

                        $row_name = $field_group_name;

                        if ( !isset( $pods_tab_form ) )
                            $row_name = 'field_data[' . $pods_i . '][' . $field_group_name . ']';

                        $value = $field_group_option[ 'default' ];

                        if ( isset( $field_group_option[ 'value' ] ) && 0 < strlen( $field_group_option[ 'value' ] ) )
                            $value = $field_group_option[ 'value' ];
                        else
                            $value = pods_var_raw( $field_group_name, $field, $value );

                        ?>
                        <li class="<?php echo $depends_option; ?>">
                            <?php echo PodsForm::field( $row_name, $value, $field_group_option[ 'type' ], $field_group_option ); ?>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
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
