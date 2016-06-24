<?php
    $attributes = array();
    $attributes[ 'type' ] = 'text';
    $attributes[ 'value' ] = $value;
    $attributes[ 'tabindex' ] = 2;
    $attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

    if ( pods_var( 'readonly', $options, false ) ) {
        $attributes[ 'readonly' ] = 'READONLY';

        $attributes[ 'class' ] .= ' pods-form-ui-read-only';
    }
    
    $show_preview = (int) pods_v( $form_field_type . '_show_preview', $options, 0 );
?>
    <input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />

<?php 
if ( 1 == $show_preview ) {
?>
    <p class="howto">
        <?php _e( 'Preview', 'pods' );?>
    </p>
    <div class="pods-oembed-preview">
        <?php echo PodsForm::field_method( $form_field_type, 'display', $value, $name, $options ); ?>
    </div>
<?php
}
PodsForm::regex( $form_field_type, $options );
