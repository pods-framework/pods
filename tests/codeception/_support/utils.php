<?php
if ( ! function_exists( 'set_object_state' ) ) {
	function set_object_state( array $properties ) {
		$obj = new stdClass();
		foreach ( $properties as $key => $value ) {
			$obj->{$key} = $value;
		}

		return $obj;
	}
}
