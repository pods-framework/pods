<div class="wrap pods-admin">
    <div id="icon-pods" class="icon32"><br /></div>
    <form action="" method="post" class="pods-submittable">
        <div class="pods-submittable-fields">
            <input type="hidden" name="action" value="pods_admin" />
            <input type="hidden" name="method" value="save_component_settings" />
            <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'pods-save_component_settings' ); ?>" />
            <input type="hidden" name="component" value="<?php echo esc_attr( $component ); ?>" />

            <h2><?php _e( 'Settings', 'pods' ); ?>: <?php echo $component_label; ?></h2>

            <?php
                $depends_on = false;

                foreach ( $options as $field_name => $field_option ) {
                    $field_option = PodsForm::options_setup();

                    $depends = PodsForm::dependencies( $field_option );

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

                        $value = $field_option[ 'default' ];

                        if ( isset( $field_option[ 'value' ] ) && 0 < strlen( $field_option[ 'value' ] ) )
                            $value = $field_option[ 'value' ];
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

                                    $depends_option = PodsForm::dependencies( $field_group_option );

                                    $row_name = $field_group_name;

                                    $value = $field_group_option[ 'default' ];

                                    if ( isset( $field_group_option[ 'value' ] ) && 0 < strlen( $field_group_option[ 'value' ] ) )
                                        $value = $field_group_option[ 'value' ];
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
            ?>
        </div>
    </form>
</div>