<div class="wrap pods-admin">
    <script>
        var PODS_URL = '<?php echo PODS_URL; ?>';
    </script>
    <div id="icon-pods" class="icon32"><br /></div>

    <form action="" method="post" class="pods-submittable">
        <div class="pods-submittable-fields">
            <input type="hidden" name="action" value="pods_admin_components" />
            <input type="hidden" name="component" value="<?php echo $component; ?>" />
            <input type="hidden" name="method" value="<?php echo $method; ?>" />
            <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('pods-component-' . $component . '-' . $method ); ?>" />

            <h2 class="italicized"><?php _e('Roles &amp; Capabilities: Edit Role', 'pods'); ?></h2>

            <img src="<?php echo PODS_URL; ?>/ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

            <p><?php _e( 'Choose below which Capabilities you would like this existing user role to have.', 'pods' ); ?></p>

            <div class="stuffbox">
                <h3><label for="link_name"><?php _e( 'Assign the Capabilities for', 'pods' ); ?> <strong><?php echo $role_label; ?></strong></label></h3>

                <div class="inside pods-manage-field pods-dependency">
                    <div class="pods-field-option-group">
                        <p>
                            <a href="#toggle" class="button" id="toggle_all"><?php _e( 'Toggle All Capabilities on / off', 'pods' ); ?></a>
                        </p>

                        <div class="pods-pick-values pods-pick-checkbox pods-zebra">
                            <ul>
                                <?php
                                $zebra = false;

                                foreach ( $capabilities as $capability ) {
                                    $checked = false;

                                    if ( in_array( $capability, $roles[ 'capabilities' ] ) )
                                        $checked = true;

                                    $class = ( $zebra ? 'even' : 'odd' );

                                    $zebra = ( !$zebra );
                                    ?>
                                    <li class="pods-zebra-<?php echo $class; ?>" data-capability="<?php echo esc_attr( $capability ); ?>">
                                        <?php echo PodsForm::field( 'capabilities[' . $capability . ']', pods_var_raw( 'capabilities[' . $capability . ']', 'post', $checked ), 'boolean', array( 'boolean_yes_label' => $capability ) ); ?>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    var pods_admin_submit_callback = function ( id ) {
        document.location = 'admin.php?page=pods-component-<?php echo $component; ?>&do=create';
    }

    jQuery( function ( $ ) {
        $( document ).Pods( 'validate' );
        $( document ).Pods( 'submit' );
        $( document ).Pods( 'wizard' );
        $( document ).Pods( 'dependency' );
        $( document ).Pods( 'advanced' );
        $( document ).Pods( 'confirm' );
        $( document ).Pods( 'sluggable' );

        var toggle_all = true;
        $( '#toggle_all' ).on( 'click', function ( e ) {
            e.preventDefault();

            $( '.pods-field.pods-boolean input[type="checkbox"]' ).prop( 'checked', toggle_all );

            toggle_all = ( !toggle_all );
        } );
    } );
</script>