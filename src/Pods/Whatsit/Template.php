<?php

namespace Pods\Whatsit;

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Template class.
 *
 * @since 2.8.0
 */
class Template extends Legacy_Object {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'template';

}
