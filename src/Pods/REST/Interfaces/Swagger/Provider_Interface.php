<?php

namespace Pods\REST\Interfaces\Swagger;

/**
 * Provider interface.
 *
 * @credit The Events Calendar team - https://github.com/the-events-calendar/tribe-common
 *
 * @since 3.0
 */
interface Provider_Interface {
	/**
	 * Returns an array in the format used by Swagger 2.0.
	 *
	 * While the structure must conform to that used by v2.0 of Swagger the structure can be that of a full document
	 * or that of a document part.
	 * The intelligence lies in the "gatherer" of informations rather than in the single "providers" implementing this
	 * interface.
	 *
	 * @link http://swagger.io/
	 *
	 * @since 3.0
	 *
	 * @return array An array description of a Swagger supported component.
	 */
	public function get_documentation();
}
