<?php

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<p<?php PodsForm::attributes( $attributes, $name, $type, $options ); ?>>
	<?php pods_output_kses_exclude_p( $message ); ?>
</p>
