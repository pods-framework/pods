<?php

namespace Pods\Tools;

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

use Exception;

/**
 * Exception thrown when another plugin is conflicting with the queries a tool relies on.
 *
 * Tools like the Repair tool decide what to change based on what Pods and WP_Query return. Those
 * results can be altered by other plugins through query filters, meta filters, and object caching.
 * When that happens the tool would repair the wrong things, so it stops instead.
 *
 * @since 3.4.0
 */
class Conflict_Exception extends Exception {

}
