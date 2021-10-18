<?php

namespace Pods\Admin\Config;

/**
 * Base configuration class.
 *
 * @since 2.8.0
 */
class Base {

	/**
	 * Get list of tabs for the pod object.
	 *
	 * @since 2.8.0
	 *
	 * @param \Pods\Whatsit\Pod $pod The pod object.
	 *
	 * @return array List of tabs for the pod object.
	 */
	public function get_tabs( \Pods\Whatsit\Pod $pod ) {
		return [];
	}

	/**
	 * Get list of fields for the pod object.
	 *
	 * @since 2.8.0
	 *
	 * @param \Pods\Whatsit\Pod $pod  The pod object.
	 * @param array             $tabs The list of tabs for the pod object.
	 *
	 * @return array List of fields for the pod object.
	 */
	public function get_fields( \Pods\Whatsit\Pod $pod, array $tabs ) {
		return [];
	}
}
