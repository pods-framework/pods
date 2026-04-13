<?php

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<label<?php PodsForm::attributes( $attributes, $name, 'label' ); ?>>
	<?php
	pods_output_kses_exclude_p( $label );

	if ( 1 == pods_v( 'required', $options, pods_v( 'options', $options, $options ) ) ) {
		echo ' <abbr title="required" class="required">*</abbr>';
	}

	if ( 0 === (int) pods_v( 'grouped', $options ) && ! empty( $help ) && __( 'help', 'pods' ) !== $help ) {
		pods_help( $help );
	}
	?>
</label>
