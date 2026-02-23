<?php

namespace Pods\Whatsit;

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Object_Field class.
 *
 * @since 2.8.0
 */
class Object_Field extends Field {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'object-field';

}
