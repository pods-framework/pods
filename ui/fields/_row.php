<?php

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

PodsForm::output_label( $name, $options ) . "\n";
PodsForm::output_field( $name, $value, $type, $options, $pod, $id ) . "\n";
PodsForm::output_comment( $name, null, $options ) . "\n";
