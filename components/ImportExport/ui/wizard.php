<div class="wrap pods-admin">
    <script>
        var PODS_URL = '<?php echo PODS_URL; ?>';
    </script>
    <div id="icon-pods" class="icon32"><br /></div>

    <form action="" method="post" class="pods-submittable">
        <div class="pods-submittable-fields">
            <?php echo PodsForm::field( 'action', 'pods_admin_components', 'hidden' ); ?>
            <?php echo PodsForm::field( 'component', $component, 'hidden' ); ?>
            <?php echo PodsForm::field( 'method', $method, 'hidden' ); ?>
            <?php echo PodsForm::field( '_wpnonce', wp_create_nonce( 'pods-component-' . $component . '-' . $method ), 'hidden' ); ?>

            <h2 class="italicized"><?php _e( 'Packages: Import and Export', 'pods' ); ?></h2>

            <img src="<?php echo PODS_URL; ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

            <div id="pods-wizard-box" class="pods-wizard-steps-3" data-step-process="2">
                <div id="pods-wizard-heading">
                    <ul>
                        <li class="pods-wizard-menu-current" data-step="1">
                            <i></i> <span>1</span> <?php _e( 'Import / Export', 'pods' ); ?>
                            <em></em>
                        </li>
                        <li data-step="2">
                            <i></i> <span>2</span> <?php _e( 'Choose', 'pods' ); ?>
                            <em></em>
                        </li>
                        <li data-step="3">
                            <i></i> <span>3</span> <?php _e( 'Result', 'pods' ); ?>
                            <em></em>
                        </li>
                    </ul>
                </div>
                <div id="pods-wizard-main">
                    <div id="pods-wizard-panel-1" class="pods-wizard-panel">
                        <div class="pods-wizard-content">
                            <p><?php _e( 'Packages allow you to import and export your Pods, Fields, and other settings between different Pods sites.', 'pods' ); ?></p>
                        </div>

                        <div id="pods-wizard-options">
                            <div class="pods-wizard-option">
                                <a href="#pods-wizard-import" data-opt="import">
                                    <h2><?php _e( 'Import', 'pods' ); ?></h2>

                                    <p><?php _e( 'Provide the Package code that was exported from another site, to be imported into this Pods site.', 'pods' ); ?></p>
                                </a>

                                <p><br /></p>
                            </div>
                            <div class="pods-wizard-option">
                                <a href="#pods-wizard-export" data-opt="export">
                                    <h2><?php _e( 'Export', 'pods' ); ?></h2>

                                    <p><?php _e( 'Export your Pods, Fields, and other settings into a Package which can be pasted into another Pods site.', 'pods' ); ?></p>
                                </a>

                                <p><br /></p>
                            </div>
                        </div>
                    </div>

                    <div id="pods-wizard-panel-2" class="pods-wizard-panel">
                        <div class="pods-wizard-option-content pods-wizard-option-content-import">
                            <div class="pods-wizard-content">
                                <p><?php _e( 'Paste the Package code you received exactly as it was given to you from the other Pods site.', 'pods' ); ?></p>
                            </div>

                            <div class="stuffbox">
                                <h3><label for="link_name"><?php _e( 'Paste the Package Code', 'pods' ); ?></label></h3>

                                <div class="inside pods-manage-field pods-dependency">
                                    <div class="pods-field-option">
                                        <?php
                                            echo PodsForm::label( 'import_package', __( 'Package Code', 'pods' ), __( 'help', 'pods' ) );
                                            echo PodsForm::field( 'import_package', pods_var_raw( 'import_package', 'post' ), 'paragraph', array( 'attributes' => array( 'style' => 'width: 75%; max-width: 75%; height: 300px;' ) ) );
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pods-wizard-option-content pods-wizard-option-content-export">
                            <div class="pods-wizard-content">
                                <p><?php _e( 'Select what you would like to export from this Pods site, you will be given the Package code to then be pasted into any other Pods site.', 'pods' ); ?></p>
                            </div>

                            <div class="stuffbox">
                                <h3><label for="link_name"><?php _e( 'Select what to Export', 'pods' ); ?></label></h3>

                                <div class="inside pods-manage-field pods-dependency">
                                    <?php
                                        $_pods = pods_api()->load_pods();

                                        $data = array();

                                        foreach ( $_pods as $pod ) {
                                            $data[ $pod[ 'name' ] ] = $pod[ 'label' ] . ' (' . $pod[ 'name' ] . ')';
                                        }

                                        $exportables = array(
                                            'pods' => array(
                                                'label' => __( 'Pods &amp; Fields', 'pods' ),
                                                'help' => __( 'help', 'pods' ),
                                                'data' => $data
                                            )
                                        );

                                        $exportables = apply_filters( 'pods_packages_wizard_exportables', $exportables );

                                        foreach ( $exportables as $name => $exportable ) {
                                            if ( !is_array( $exportable ) || !isset( $exportable[ 'data' ] ) || empty( $exportable[ 'data' ] ) )
                                                continue;

                                            $options = $exportable;

                                            $options[ 'options' ] = array(
                                                'pick_format_type' => 'multi',
                                                'pick_format_multi' => 'checkbox'
                                            );
                                    ?>
                                        <div class="pods-field-option">
                                            <?php echo PodsForm::row( 'export_' . $name, pods_var_raw( 'export_' . $name, 'post' ), 'pick', $options ); ?>

                                            <p><a href="#toggle" class="button pods-wizard-toggle-all" data-toggle="<?php echo 'export_' . $name; ?>"><?php _e( 'Toggle All on / off', 'pods' ); ?></a></p>
                                        </div>
                                    <?php
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="pods-wizard-panel-3" class="pods-wizard-panel">
                        <div class="pods-wizard-option-content pods-wizard-option-content-import">
                            <div class="pods-wizard-content">
                                <p><?php _e( 'Below are the potential results of the package you are importing, please confirm you would like to continue.', 'pods' ); ?></p>
                            </div>

                            <div class="stuffbox">
                                <h3><label for="link_name"><?php _e( 'Results', 'pods' ); ?></label></h3>

                                <div class="inside">

                                </div>
                            </div>
                        </div>

                        <div class="pods-wizard-option-content pods-wizard-option-content-export">
                            <div class="pods-wizard-content">
                                <p>
                                    <?php _e( 'Below is the package code generated by your request, you can copy and paste it into any other Pods installation.', 'pods' ); ?>
                                    <br /><br />
                                    <textarea style="width:100%;height:250px;" id="export-code"></textarea>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div id="pods-wizard-actions">
                        <div id="pods-wizard-toolbar">
                            <a href="#start" id="pods-wizard-start" class="button button-secondary"><?php _e( 'Start Over', 'pods' ); ?></a> <a href="#next" id="pods-wizard-next" class="button button-primary" data-next="<?php esc_attr_e( 'Next Step', 'pods' ); ?>" data-finished="<?php esc_attr_e( 'Finished', 'pods' ); ?>" data-processing="<?php esc_attr_e( 'Processing', 'pods' ); ?>.."><?php _e( 'Next Step', 'pods' ); ?></a>
                        </div>
                        <div id="pods-wizard-finished">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
    var pods_admin_submit_callback = function ( id ) {
        console.log( id );

        return true;
        //document.location = 'admin.php?page=pods-component-<?php echo esc_js( $component ); ?>&do=create';
    }

    jQuery( function ( $ ) {
        $( document ).Pods( 'validate' );
        $( document ).Pods( 'submit' );
        $( document ).Pods( 'wizard' );
        $( document ).Pods( 'dependency' );
        $( document ).Pods( 'advanced' );
        $( document ).Pods( 'confirm' );
        $( document ).Pods( 'sluggable' );

        var toggle_all = {};

        $( '.pods-wizard-toggle-all' ).on( 'click', function ( e ) {
            e.preventDefault();

            if ( 'undefined' == typeof toggle_all[ $( this ).data( 'toggle' ) ] )
                toggle_all[ $( this ).data( 'toggle' ) ] = false;

            $( this ).closest( '.pods-field-option' ).find( '.pods-field.pods-boolean input[type="checkbox"]' ).prop( 'checked', ( !toggle_all[ $( this ).data( 'toggle' ) ] ) );

            toggle_all[ $( this ).data( 'toggle' ) ] = ( !toggle_all[ $( this ).data( 'toggle' ) ] );
        } );

        $( '#export-code' ).on( 'click', function ( e ) {
            e.preventDefault();

            $( this ).select();
        } );
    } );
</script>
