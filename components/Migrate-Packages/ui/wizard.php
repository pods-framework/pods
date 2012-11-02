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

            <h2 class="italicized"><?php _e( 'Import Pods 1.x Packages', 'pods' ); ?></h2>

            <img src="<?php echo PODS_URL; ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

            <div id="pods-wizard-box" class="pods-wizard-steps-2" data-step-process="1">
                <div id="pods-wizard-heading">
                    <ul>
                        <li class="pods-wizard-menu-current" data-step="1">
                            <i></i> <span>1</span> <?php _e( 'Import', 'pods' ); ?>
                            <em></em>
                        </li>
                        <li data-step="2">
                            <i></i> <span>2</span> <?php _e( 'Result', 'pods' ); ?>
                            <em></em>
                        </li>
                    </ul>
                </div>
                <div id="pods-wizard-main">
                    <div id="pods-wizard-panel-1" class="pods-wizard-panel">
                        <div class="pods-wizard-content">
                            <p><?php _e( 'Packages allow you to import your Pods, Fields, and other settings from any Pods 1.x sites.', 'pods' ); ?></p>
                        </div>

                        <div class="stuffbox">
                            <h3><label for="link_name"><?php _e( 'Paste the Package Code', 'pods' ); ?></label></h3>

                            <div class="inside pods-manage-field pods-dependency">
                                <div class="pods-field-option">
                                    <?php
                                        echo PodsForm::field( 'import_package', pods_var_raw( 'import_package', 'post' ), 'paragraph', array( 'attributes' => array( 'style' => 'width: 94%; max-width: 94%; height: 300px;' ) ) );
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="pods-wizard-panel-2" class="pods-wizard-panel">
                        <div class="pods-wizard-content">
                            <p><?php _e( 'Below are the potential results of the package you are importing, please confirm you would like to continue.', 'pods' ); ?></p>
                        </div>

                        <div class="stuffbox">
                            <h3><label for="link_name"><?php _e( 'Results', 'pods' ); ?></label></h3>

                            <div class="inside">

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

        jQuery( '#pods-wizard-panel-2 div.inside' ).html( '<pre>' + id + '</pre>' );

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
