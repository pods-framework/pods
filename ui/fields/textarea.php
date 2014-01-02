<?php
    $type = 'textarea';
    $attributes = array();
    $attributes[ 'tabindex' ] = 2;
    $attributes = Pods_Form::merge_attributes( $attributes, $name, $form_field_type, $options );

    if ( pods_var( 'readonly', $options, false ) ) {
        $attributes[ 'readonly' ] = 'READONLY';

        $attributes[ 'class' ] .= ' pods-form-ui-read-only';
    }

	$rows = (int) pods_v( 'paragraph_rows', $options, 0 );

	if ( 0 < $rows ) {
		$attributes[ 'rows' ] = $rows;
	}
?>
    <textarea<?php Pods_Form::attributes( $attributes, $name, $form_field_type, $options ); ?>><?php echo esc_textarea( $value ); ?></textarea>
<?php
    Pods_Form::regex( $form_field_type, $options );