<div class="wrap pods-admin">
    <script>
        var PODS_URL = '<?php echo PODS_URL; ?>';
    </script>
    <div id="icon-pods" class="icon32"><br /></div>

    <form action="" method="post" class="pods-submittable">
        <div class="pods-submittable-fields">
            <input type="hidden" name="action" value="pods_admin" />
            <input type="hidden" name="method" value="component_gravity_forms_add" />
            <input type="hidden" name="component" value="gravity-forms" />
            <input type="hidden" name="" value="gravity_forms" />
            <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('pods-component_gravity_forms_add'); ?>" />

            <h2 class="italicized"><?php _e('Add New Gravity Form Mapping', 'pods'); ?></h2>

            <div id="poststuff">
                <img src="<?php echo PODS_URL; ?>/ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

                <div class="pods-field-option">
                    <?php echo PodsForm::label( 'form', __( 'Gravity Form', 'pods' ), __( 'help', 'pods' ) ); ?>
                    <?php echo PodsForm::field( 'form', pods_var( 'form', 'post' ), 'pick', array( 'data' => $forms, 'class' => 'pods-validate pods-validate-required' ) ); ?>
                </div>

                <div class="pods-field-option">
                    <?php echo PodsForm::label( 'pod', __( 'Pod', 'pods' ), __( 'help', 'pods' ) ); ?>
                    <?php echo PodsForm::field( 'pod', pods_var( 'pod', 'post' ), 'pick', array( 'data' => $pods, 'class' => 'pods-validate pods-validate-required' ) ); ?>
                </div>

                <p class="submit">
                    <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
                    <button type="submit" class="button-primary"><?php _e('Continue', 'pods'); ?></button>
                </p>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    jQuery( function ( $ ) {
        $( document ).Pods( 'validate' );
        $( document ).Pods( 'submit' );
        $( document ).Pods( 'confirm' );
    } );

    pods_admin_submit_callback = function ( id ) {
        document.location = '<?php echo pods_var_update( array( 'action' => 'edit' ) ); ?>&id=' + id;
    }
</script>