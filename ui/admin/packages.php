<div class="wrap pods-admin">
    <script>
        var PODS_URL = '<?php echo PODS_URL; ?>';
    </script>
    <div id="icon-pods" class="icon32"><br /></div>

    <form action="" method="post" class="pods-submittable">
        <div class="pods-submittable-fields">
            <?php echo PodsForm::field( 'action', 'pods_admin', 'hidden' ); ?>
            <?php echo PodsForm::field( 'method', $method, 'hidden' ); ?>
            <?php echo PodsForm::field( '_wpnonce', wp_create_nonce( 'pods-' .$method ), 'hidden' ); ?>
            <?php echo PodsForm::field( 'import_export', '', 'hidden', array( 'attributes' => array( 'id' => 'pods_import_export' ) ) ); ?>

            <h2 class="italicized"><?php _e( 'Import and Export Packages', 'pods' ); ?></h2>

            <img src="<?php echo PODS_URL; ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

            <div id="pods-wizard-box" class="pods-wizard-steps-2 pods-wizard-hide-first">
                <div id="pods-wizard-heading">
                    <ul>
                        <li class="pods-wizard-menu-current" data-step="1">
                            <i></i>
                            <span>1</span> <?php _e( 'Choose', 'pods' ); ?>
                            <em></em>
                        </li>
                        <li data-step="2">
                            <i></i>
                            <span>2</span> <?php _e( 'Import / Export', 'pods' ); ?>
                            <em></em>
                        </li>
                    </ul>
                </div>
                <div id="pods-wizard-main">
                    <div id="pods-wizard-panel-1" class="pods-wizard-panel">
                        <div class="pods-wizard-content">
                            <p><?php _e( 'Packages are used to import and export between different Pods installations. ', 'pods' ); ?></p>
                        </div>
                        <div id="pods-wizard-options">
                            <div class="pods-wizard-option">
                                <a href="#pods-wizard-import" data-opt="import">
                                    <h2><?php _e( 'Import', 'pods' ); ?></h2>

                                    <p><?php _e( 'Import your content and settings into this site.', 'pods' ); ?></p>
                                </a>

                                <p><br /></p>
                            </div>
                            <div class="pods-wizard-option">
                                <a href="#pods-wizard-export" data-opt="export">
                                    <h2><?php _e( 'Export', 'pods' ); ?></h2>

                                    <p><?php _e( 'Export your content and settings into any other site.', 'pods' ); ?></p>
                                </a>

                                <p><br /></p>
                            </div>
                        </div>
                    </div>
                    <div id="pods-wizard-panel-2" class="pods-wizard-panel">
                        <div class="pods-wizard-option-content" id="pods-wizard-import">
                            <div class="pods-wizard-content">
                                <p><?php _e( 'Paste your package that you exported from any other site and continue to import.', 'pods' ); ?></p>
                            </div>

                            <?php echo PodsForm::field( 'import_package', pods_var_raw( 'import_package', 'post', true ), 'paragraph', array( 'paragraph_format_type' => 'plain' ) ); ?>

                            <div class="stuffbox" id="import">
                                <h3><label for="link_name"><?php _e( 'Import Results', 'pods' ); ?></label></h3>

                                <div class="inside pods-manage-field">

                                </div>
                            </div>
                        </div>

                        <div class="pods-wizard-option-content" id="pods-wizard-export">
                            <div class="pods-wizard-content">
                                <p><?php _e( 'Choose the content and settings you would like to export below.', 'pods' ); ?></p>
                            </div>

                            <div class="stuffbox">
                                <h3><label for="link_name"><?php _e( 'Export Content and Settings', 'pods' ); ?></label>
                                </h3>

                                <div class="inside pods-manage-field">
                                    <div class="pods-field-option-group">
                                        <p class="pods-field-option-group-label">
                                            <?php _e( 'Pods and their Fields', 'pods' ); ?>
                                        </p>

                                        <div class="pods-pick-values pods-pick-checkbox">
                                            <?php
                                                $objects = $api->load_pods();

                                                if ( !empty( $objects ) ) {
                                            ?>
                                                <ul>
                                                    <?php
                                                        foreach ( $objects as $object ) {
                                                            $name = pods_var_raw( 'name', $object );
                                                            $label = pods_var_raw( 'label', $object, ucwords( str_replace( '_', ' ', $name ) ) );
                                                    ?>
                                                        <li>
                                                            <div class="pods-field pods-boolean">
                                                                <?php echo PodsForm::field( 'pods[' . $name . ']', pods_var_raw( 'pods[' . $name . ']', 'post', true ), 'boolean', array( 'boolean_yes_label' => $label . ' (' . $name . ')' ) ); ?>
                                                            </div>
                                                        </li>
                                                    <?php
                                                        }
                                                    ?>
                                                </ul>
                                            <?php
                                                }
                                                else {
                                            ?>
                                                <p class="padded"><?php _e( 'No Pods found.', 'pods' ); ?></p>
                                            <?php
                                                }
                                            ?>
                                        </div>
                                    </div>

                                    <div class="pods-field-option-group">
                                        <p class="pods-field-option-group-label">
                                            <?php _e( 'Templates', 'pods' ); ?>
                                        </p>

                                        <div class="pods-pick-values pods-pick-checkbox">
                                            <?php
                                                $objects = $api->load_templates();

                                                if ( !empty( $objects ) ) {
                                            ?>
                                                <ul>
                                                    <?php
                                                        foreach ( $objects as $object ) {
                                                            $name = pods_var_raw( 'name', $object );
                                                            $label = pods_var_raw( 'label', $object, ucwords( str_replace( '_', ' ', $name ) ) );
                                                    ?>
                                                        <li>
                                                            <div class="pods-field pods-boolean">
                                                                <?php echo PodsForm::field( 'templates[' . $name . ']', pods_var_raw( 'templates[' . $name . ']', 'post', true ), 'boolean', array( 'boolean_yes_label' => $label . ' (' . $name . ')' ) ); ?>
                                                            </div>
                                                        </li>
                                                    <?php
                                                        }
                                                    ?>
                                                </ul>
                                            <?php
                                                }
                                                else {
                                            ?>
                                                <p class="padded"><?php _e( 'No Templates found.', 'pods' ); ?></p>
                                            <?php
                                                }
                                            ?>
                                        </div>
                                    </div>

                                    <div class="pods-field-option-group">
                                        <p class="pods-field-option-group-label">
                                            <?php _e( 'Pages', 'pods' ); ?>
                                        </p>

                                        <div class="pods-pick-values pods-pick-checkbox">
                                            <?php
                                                $objects = $api->load_pages();

                                                if ( !empty( $objects ) ) {
                                            ?>
                                                <ul>
                                                    <?php
                                                        foreach ( $objects as $object ) {
                                                            $name = pods_var_raw( 'name', $object );
                                                            $label = pods_var_raw( 'label', $object, ucwords( str_replace( '_', ' ', $name ) ) );
                                                    ?>
                                                        <li>
                                                            <div class="pods-field pods-boolean">
                                                                <?php echo PodsForm::field( 'pages[' . $name . ']', pods_var_raw( 'pages[' . $name . ']', 'post', true ), 'boolean', array( 'boolean_yes_label' => $label . ' (' . $name . ')' ) ); ?>
                                                            </div>
                                                        </li>
                                                    <?php
                                                        }
                                                    ?>
                                                </ul>
                                            <?php
                                                }
                                                else {
                                            ?>
                                                <p class="padded"><?php _e( 'No Pages found.', 'pods' ); ?></p>
                                            <?php
                                                }
                                            ?>
                                        </div>
                                    </div>

                                    <div class="pods-field-option-group">
                                        <p class="pods-field-option-group-label">
                                            <?php _e( 'Helpers', 'pods' ); ?>
                                        </p>

                                        <div class="pods-pick-values pods-pick-checkbox">
                                            <?php
                                                $objects = $api->load_pages();

                                                if ( !empty( $objects ) ) {
                                            ?>
                                                <ul>
                                                    <?php
                                                        foreach ( $objects as $object ) {
                                                            $name = pods_var_raw( 'name', $object );
                                                            $label = pods_var_raw( 'label', $object, ucwords( str_replace( '_', ' ', $name ) ) );
                                                    ?>
                                                        <li>
                                                            <div class="pods-field pods-boolean">
                                                                <?php echo PodsForm::field( 'helpers[' . $name . ']', pods_var_raw( 'helpers[' . $name . ']', 'post', true ), 'boolean', array( 'boolean_yes_label' => $label . ' (' . $name . ')' ) ); ?>
                                                            </div>
                                                        </li>
                                                    <?php
                                                        }
                                                    ?>
                                                </ul>
                                            <?php
                                                }
                                                else {
                                            ?>
                                                <p class="padded"><?php _e( 'No Helpers found.', 'pods' ); ?></p>
                                            <?php
                                                }
                                            ?>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="pods-wizard-actions">
                        <div id="pods-wizard-toolbar">
                            <a href="#start" id="pods-wizard-start" class="button button-secondary"><?php _e( 'Start Over', 'pods' ); ?></a>
                            <a href="#next" id="pods-wizard-next" class="button button-primary" data-next="<?php esc_attr_e( 'Next Step', 'pods' ); ?>" data-finished="<?php esc_attr_e( 'Finished', 'pods' ); ?>" data-processing="<?php esc_attr_e( 'Processing', 'pods' ); ?>.."><?php _e( 'Next Step', 'pods' ); ?></a>
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
        // handle import
        if ( 'import' == jQuery( '#pods_import_export' ).val() ) {
            jQuery( '#import' ).html( id );
            jQuery( '#import' ).show();
        }
        // handle export
        else if ( 'export' == jQuery( '#pods_import_export' ).val() ) {
            jQuery( '#export' ).val( id );
            jQuery( '#export' ).show();
        }
    }

    var pods_admin_option_select_callback = function ( $opt ) {
        jQuery( '#export, #import' ).hide();
        jQuery( '#pods_import_export' ).val( $opt.data( 'opt' ) );
    }

    jQuery( function ( $ ) {
        $( document ).Pods( 'validate' );
        $( document ).Pods( 'submit' );
        $( document ).Pods( 'wizard' );
        $( document ).Pods( 'dependency' );
        $( document ).Pods( 'advanced' );
        $( document ).Pods( 'confirm' );
    } );
</script>