<div class="wrap pods-admin">
    <div id="icon-pods" class="icon32"><br /></div>
    <form action="" method="post" class="pods-submittable">
        <div class="pods-submittable-fields">
            <?php echo PodsForm::field( 'action', 'pods_admin_components', 'hidden' ); ?>
            <?php echo PodsForm::field( 'component', $component, 'hidden' ); ?>
            <?php echo PodsForm::field( 'method', 'settings', 'hidden' ); ?>
            <?php echo PodsForm::field( '_wpnonce', wp_create_nonce( 'pods-component-' . $component . '-settings' ), 'hidden' ); ?>

            <h2><?php _e( 'Settings', 'pods' ); ?>: <?php echo $component_label; ?></h2>

            <?php
                if ( isset( $_GET[ 'do' ] ) )
                    pods_message( __( 'Settings saved successfully.', 'pods' ) );
            ?>

            <table class="form-table pods-manage-field">
                <?php
                    $depends_on = false;

                    foreach ( $options as $field_name => $field_option ) {
                        $field_option = PodsForm::field_setup( $field_option, null, $field_option[ 'type' ] );

                        $depends = PodsForm::dependencies( $field_option );

                        if ( ( !empty( $depends_on ) || !empty( $depends ) ) && $depends_on != $depends ) {
                            if ( !empty( $depends_on ) ) {
                ?>
                    </tbody>
                <?php
                            }

                            if ( !empty( $depends ) ) {
                ?>
                    <tbody class="pods-field-option-container <?php echo $depends; ?>">
                <?php
                            }
                        }

                        if ( !is_array( $field_option[ 'group' ] ) ) {
                            $value = pods_var_raw( $field_name, $settings, $field_option[ 'default' ] );
                ?>
                    <tr valign="top" class="pods-field-option" id="pods-setting-<?php echo $field_name; ?>">
                        <th>
                            <?php echo PodsForm::label( 'pods_setting_' . $field_name, $field_option[ 'label' ], $field_option[ 'help' ], $field_option ); ?>
                        </th>
                        <td>
                            <?php echo PodsForm::field( 'pods_setting_' . $field_name, $value, $field_option[ 'type' ], $field_option ); ?>
                        </td>
                    </tr>
                <?php
                        }
                        else {
                ?>
                    <tr valign="top" class="pods-field-option-group" id="pods-setting-<?php echo $field_name; ?>">
                        <th class="pods-field-option-group-label">
                            <?php echo $field_option[ 'label' ]; ?>
                        </th>
                        <td class="pods-pick-values pods-pick-checkbox">
                            <ul>
                                <?php
                                    foreach ( $field_option[ 'group' ] as $field_group_name => $field_group_option ) {
                                        $field_group_option = PodsForm::field_setup( $field_group_option, null, $field_group_option[ 'type' ] );

                                        if ( 'boolean' != $field_group_option[ 'type' ] )
                                            continue;

                                        $field_group_option[ 'boolean_yes_label' ] = $field_group_option[ 'label' ];

                                        $depends_option = PodsForm::dependencies( $field_group_option );

                                        $value = pods_var_raw( $field_group_name, $settings, $field_group_option[ 'default' ] );
                                ?>
                                    <li class="<?php echo $depends_option; ?>">
                                        <?php echo PodsForm::field( 'pods_setting_' . $field_group_name, $value, $field_group_option[ 'type' ], $field_group_option ); ?>
                                    </li>
                                <?php
                                    }
                                ?>
                            </ul>
                        </td>
                    </tr>
                <?php
                        }

                        if ( false !== $depends_on || !empty( $depends ) )
                            $depends_on = $depends;
                    }

                    if ( !empty( $depends_on ) ) {
                ?>
                    </tbody>
                <?php
                    }
                ?>
            </table>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'pods' ); ?>">
                <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
            </p>
        </div>
    </form>
</div>

<script type="text/javascript">
    jQuery( function ( $ ) {
        $( document ).Pods( 'validate' );
        $( document ).Pods( 'submit' );
        $( document ).Pods( 'dependency' );
        $( document ).Pods( 'confirm' );
        $( document ).Pods( 'exit_confirm' );
    } );

    var pods_admin_submit_callback = function ( id ) {
        document.location = '<?php echo pods_slash( pods_var_update( array( 'do' => 'save' ) ) ); ?>';
    }
</script>