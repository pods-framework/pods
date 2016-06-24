<?php
    $attributes = array();
    $attributes[ 'type' ] = 'text';
    $attributes[ 'value' ] = $value;
    $attributes[ 'tabindex' ] = 2;
    $attributes[ 'class' ] .= ' regular-text';
    $attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

    if ( pods_var( 'readonly', $options, false ) ) {
        $attributes[ 'readonly' ] = 'READONLY';

        $attributes[ 'class' ] .= ' pods-form-ui-read-only';
    }
?>
    <input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />

    <p class="howto">
        <?php _e( 'Preview', 'pods' );?>
    </p>
    <div>
    <?php
            $embed = $GLOBALS[ 'wp_embed' ];
            $preview = $embed->run_shortcode( $value );
            $preview = $embed->autoembed( $preview );
            echo $preview;
    ?>
    </div>
<?php
PodsForm::regex( $form_field_type, $options );