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
            <?php echo PodsForm::field( 'cleanup', 0, 'hidden', array( 'attributes' => array( 'id' => 'pods_cleanup' ) ) ); ?>

            <h2 class="italicized"><?php _e( 'Migrate: Import from Custom Post Type UI', 'pods' ); ?></h2>

            <img src="<?php echo PODS_URL; ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

            <div id="pods-wizard-box" class="pods-wizard-steps-2 pods-wizard-hide-first">
                <div id="pods-wizard-heading">
                    <ul>
                        <li class="pods-wizard-menu-current" data-step="1">
                            <i></i> <span>1</span> <?php _e( 'Setup', 'pods' ); ?>
                            <em></em>
                        </li>
                        <li data-step="2">
                            <i></i> <span>2</span> <?php _e( 'Migrate', 'pods' ); ?>
                            <em></em>
                        </li>
                    </ul>
                </div>
                <div id="pods-wizard-main">
                    <div id="pods-wizard-panel-1" class="pods-wizard-panel">
                        <div class="pods-wizard-content">
                            <p><?php _e( 'Custom Post Type UI provides an interface to create Custom Post Types and Custom Taxonomies. You can import these and their settings directly into Pods 2.0', 'pods' ); ?></p>
                        </div>
                        <div id="pods-wizard-options">
                            <div class="pods-wizard-option">
                                <a href="#pods-wizard-import" data-opt="0">
                                    <h2><?php _e( 'Import Only', 'pods' ); ?></h2>

                                    <p><?php _e( 'This will import your Custom Post Types and Taxonomies.', 'pods' ); ?></p>
                                </a>

                                <p><br /></p>
                            </div>
                            <div class="pods-wizard-option">
                                <a href="#pods-wizard-import-clean" data-opt="1">
                                    <h2><?php _e( 'Import and Clean Up', 'pods' ); ?></h2>

                                    <p><?php _e( 'This will import your Custom Post Types and Taxonomies, and then remove them from Custom Post Type UI.', 'pods' ); ?></p>
                                </a>

                                <p><br /></p>
                            </div>
                        </div>
                    </div>
                    <div id="pods-wizard-panel-2" class="pods-wizard-panel">
                        <div class="pods-wizard-content">
                            <p><?php _e( 'Choose below which Custom Post Types and Taxonomies you want to import into Pods 2.0', 'pods' ); ?></p>
                        </div>

                        <div class="stuffbox">
                            <h3><label for="link_name"><?php _e( 'Choose Post Types to Import', 'pods' ); ?></label></h3>

                            <div class="inside pods-manage-field pods-dependency">
                                <?php
                                if ( !empty( $post_types ) ) {
                                    ?>
                                    <div class="pods-field-option-group">
                                        <p class="pods-field-option-group-label">
                                            <?php _e( 'Available Post Types', 'pods' ); ?>
                                        </p>

                                        <div class="pods-pick-values pods-pick-checkbox">
                                            <ul>
                                                <?php
                                                foreach ( $post_types as $post_type ) {
                                                    $post_type_name = pods_var_raw( 'name', $post_type );
                                                    $post_type_label = pods_var_raw( 'label', $post_type, ucwords( str_replace( '_', ' ', $post_type_name ) ) );
                                                    ?>
                                                    <li>
                                                        <div class="pods-field pods-boolean">
                                                            <?php echo PodsForm::field( 'post_type[' . $post_type_name . ']', pods_var_raw( 'post_type[' . $post_type_name . ']', 'post', true ), 'boolean', array( 'boolean_yes_label' => $post_type_label . ' (' . $post_type_name . ')' ) ); ?>
                                                        </div>
                                                    </li>
                                                    <?php
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php
                                }
                                else {
                                    ?>
                                    <p class="padded"><?php _e( 'No Post Types were found.', 'pods' ); ?></p>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>

                        <div class="stuffbox">
                            <h3><label for="link_name"><?php _e( 'Choose Taxonomies to Import', 'pods' ); ?></label></h3>

                            <div class="inside pods-manage-field pods-dependency">
                                <?php
                                if ( !empty( $taxonomies ) ) {
                                    ?>
                                    <div class="pods-field-option-group">
                                        <p class="pods-field-option-group-label">
                                            <?php _e( 'Available Taxonomies', 'pods' ); ?>
                                        </p>

                                        <div class="pods-pick-values pods-pick-checkbox">
                                            <ul>
                                                <?php
                                                foreach ( $taxonomies as $taxonomy ) {
                                                    $taxonomy_name = pods_var_raw( 'name', $taxonomy );
                                                    $taxonomy_label = pods_var_raw( 'label', $taxonomy, ucwords( str_replace( '_', ' ', $taxonomy_name ) ) );
                                                    ?>
                                                    <li>
                                                        <?php echo PodsForm::field( 'taxonomy[' . $taxonomy_name . ']', pods_var_raw( 'taxonomy[' . $taxonomy_name . ']', 'post', true ), 'boolean', array( 'boolean_yes_label' => $taxonomy_label . ' (' . $taxonomy_name . ')' ) ); ?>
                                                    </li>
                                                    <?php
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php
                                }
                                else {
                                    ?>
                                    <p class="padded"><?php _e( 'No Taxonomies were found.', 'pods' ); ?></p>
                                    <?php
                                }
                                ?>
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
        id = parseInt( id );
        document.location = 'admin.php?page=pods&do=create';
    }

    var pods_admin_option_select_callback = function ( $opt ) {
        jQuery( '#pods_cleanup' ).val( $opt.data( 'opt' ) );
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
