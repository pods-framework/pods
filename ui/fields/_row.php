<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

echo PodsForm::label( $name, $options ) . "\n";
echo PodsForm::field( $name, $value, $type, $options, $pod, $id ) . "\n";
echo PodsForm::comment( $name, null, $options ) . "\n";
