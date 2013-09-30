<?php
wp_enqueue_style( 'pods-form', false, array(), false, true );

if ( !wp_script_is( 'pods', 'done' ) )
    wp_print_scripts( 'pods' );

// unset fields
foreach ( $fields as $k => $field ) {
    if ( in_array( $field[ 'name' ], array( 'created', 'modified' ) ) )
        unset( $fields[ $k ] );
    elseif ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field[ 'options' ], $fields, $pod, $pod->id() ) ) {
        if ( pods_var( 'hidden', $field[ 'options' ], false ) )
            $fields[ $k ][ 'type' ] = 'hidden';
        elseif ( pods_var( 'read_only', $field[ 'options' ], false ) )
            $fields[ $k ][ 'readonly' ] = true;
        else
            unset( $fields[ $k ] );
    }
    elseif ( !pods_has_permissions( $field[ 'options' ] ) ) {
        if ( pods_var( 'hidden', $field[ 'options' ], false ) )
        $fields[ $k ][ 'type' ] = 'hidden';
        elseif ( pods_var( 'read_only', $field[ 'options' ], false ) )
            $fields[ $k ][ 'readonly' ] = true;
    }
}

$submittable_fields = $fields;

foreach ( $submittable_fields as $k => $field ) {
    if ( pods_var( 'readonly', $field, false ) )
        unset( $submittable_fields[ $k ] );
}

$uri_hash = wp_create_nonce( 'pods_uri_' . $_SERVER[ 'REQUEST_URI' ] );
$field_hash = wp_create_nonce( 'pods_fields_' . implode( ',', array_keys( $submittable_fields ) ) );

$uid = @session_id();

if ( is_user_logged_in() )
    $uid = 'user_' . get_current_user_id();

$nonce = wp_create_nonce( 'pods_form_' . $pod->pod . '_' . $uid . '_' . $pod->id() . '_' . $uri_hash . '_' . $field_hash );

if ( isset( $_POST[ '_pods_nonce' ] ) ) {
    try {
        $id = $pod->api->process_form( $_POST, $pod, $fields, $thank_you );
    }
    catch ( Exception $e ) {
        echo '<div class="pods-message pods-message-error">' . $e->getMessage() . '</div>';
    }
}
?>
<form action="" method="post" class="pods-submittable pods-form pods-form-front pods-form-pod-<?php echo $pod->pod; ?> pods-submittable-ajax" data-location="<?php echo $thank_you; ?>">
    <div class="pods-submittable-fields">
        <?php echo PodsForm::field( 'action', 'pods_admin', 'hidden' ); ?>
        <?php echo PodsForm::field( 'method', 'process_form', 'hidden' ); ?>
        <?php echo PodsForm::field( 'do', ( 0 < $pod->id() ? 'save' : 'create' ), 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_nonce', $nonce, 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_pod', $pod->pod, 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_id', $pod->id(), 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_uri', $uri_hash, 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_form', implode( ',', array_keys( $fields ) ), 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_location', $_SERVER[ 'REQUEST_URI' ], 'hidden' ); ?>

        <ul class="pods-form-fields">
            <?php
                foreach ( $fields as $field ) {
                    if ( 'hidden' == $field[ 'type' ] )
                        continue;

                    do_action( 'pods_form_pre_field', $field, $fields, $pod );
            ?>
                <li class="pods-field <?php echo 'pods-form-ui-row-type-' . $field[ 'type' ] . ' pods-form-ui-row-name-' . PodsForm::clean( $field[ 'name' ], true ); ?>">
                    <div class="pods-field-label">
                        <?php echo PodsForm::label( 'pods_field_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ], $field ); ?>
                    </div>

                    <div class="pods-field-input">
                        <?php echo PodsForm::field( 'pods_field_' . $field[ 'name' ], $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) ), $field[ 'type' ], $field, $pod, $pod->id() ); ?>

                        <?php echo PodsForm::comment( 'pods_field_' . $field[ 'name' ], null, $field ); ?>
                    </div>
                </li>
            <?php
                }
            ?>
        </ul>

        <?php
            foreach ( $fields as $field ) {
                if ( 'hidden' != $field[ 'type' ] )
                    continue;

                echo PodsForm::field( 'pods_field_' . $field[ 'name' ], $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) ), 'hidden' );
           }
        ?>

        <p class="pods-submit">
            <img class="waiting" src="<?php echo admin_url() . '/images/wpspin_light.gif' ?>" alt="">
            <input type="submit" value=" <?php echo esc_attr( $label ); ?> " class="pods-submit-button" />

            <?php do_action( 'pods_form_after_submit', $pod, $fields ); ?>
        </p>
    </div>
</form>

<script type="text/javascript">
    if ( 'undefined' == typeof pods_form_init ) {
        var pods_form_init = true;

        if ( 'undefined' == typeof ajaxurl ) {
            var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
        }

        jQuery( function ( $ ) {
            $( document ).Pods( 'validate' );
            $( document ).Pods( 'submit' );
        } );
    }
</script>
