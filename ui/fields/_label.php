<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?>
<label<?php PodsForm::attributes( $attributes, $name, 'label' ); ?>>
	<?php
	echo pods_kses_exclude_p( $label );

	if ( 1 == pods_v( 'required', $options, pods_v( 'options', $options, $options ) ) ) {
		echo ' <abbr title="required" class="required">*</abbr>';
	}

	if ( 0 === (int) pods_v( 'grouped', $options ) && ! empty( $help ) && __( 'help', 'pods' ) !== $help ) {
		pods_help( $help );
	}
	?>
</label>
