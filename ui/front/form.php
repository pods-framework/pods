<?php
wp_enqueue_script( 'pods', false, array( 'jquery' ), false, true );
wp_enqueue_style( 'pods-form', false, array(), false, true );

// unset fields
foreach ( $fields as $k => $field ) {
    if ( in_array( $field[ 'name' ], array( 'created', 'modified' ) ) )
        unset( $fields[ $k ] );
    elseif ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $fields, $pod, $pod->id() ) )
        unset( $fields[ $k ] );
}

// This isn't ready yet
$uri_hash = wp_create_nonce( 'pods_uri_' . $_SERVER[ 'REQUEST_URI' ] );
$field_hash = wp_create_nonce( 'pods_fields_' . implode( ',', array_keys( $fields ) ) );

$nonce = wp_create_nonce( 'pods_form_' . $pod->pod . '_' . session_id() . '_' . $pod->id() . '_' . $uri_hash . '_' . $field_hash );

if ( isset( $_POST[ '_pods_nonce' ] ) ) {
    try {
        $id = $pod->api->process_form( $_POST, $pod, $fields, $thank_you );
    }
    catch ( Exception $e ) {
        echo '<div class="pods-message pods-message-error">' . $e->getMessage() . '</div>';
    }
}
?>
<form action="<?php echo pods_var_update( array( '_p_submitted' => 1 ) ); ?>" method="post" class="pods-submittable pods-form pods-form-front pods-form-pod-<?php echo $pod->pod; ?>" data-location="<?php echo pods_var_update( array( 'success' => true ) ) ?>">
    <div class="pods-submittable-fields">
        <?php echo PodsForm::field( '_pods_nonce', $nonce, 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_pod', $pod->pod, 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_id', $pod->id(), 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_uri', $uri_hash, 'hidden' ); ?>
        <?php echo PodsForm::field( '_pods_form', implode( ',', array_keys( $fields ) ), 'hidden' ); ?>

        <ul class="pods-form-fields">
            <?php
                foreach ( $fields as $field ) {
                    do_action( 'pods_form_pre_field', $field, $fields, $pod );
            ?>
                <li class="pods-field <?php echo 'pods-form-ui-row-type-' . $field[ 'type' ] . ' pods-form-ui-row-name-' . Podsform::clean( $field[ 'name' ], true ); ?>">
                    <div class="pods-field-label">
                        <?php echo PodsForm::label( 'pods_field_' . $field[ 'name' ], $field ); ?>
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

        <p class="pods-submit">
            <img class="waiting" src="<?php echo admin_url() . '/images/wpspin_light.gif' ?>" alt="">
            <input type="submit" value=" <?php echo esc_attr( $label ); ?> " class="pods-submit-button" />
d
            <?php do_action( 'pods_form_after_submit', $pod, $fields ); ?>
        </p>
    </div>
</form>

<script type="text/javascript">
    jQuery( function ( $ ) {
        $( document ).Pods( 'validate' );
        $( document ).Pods( 'submit' );
    } );
</script>