<?php

namespace Pods\Whatsit;

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Page class.
 *
 * @since 2.8.0
 */
class Page extends Legacy_Object {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'page';

}
