<?php

namespace Tribe\Values;

trait Value_Update {

	/**
	 * @inheritDoc
	 */
	public function get_setters() {

		if ( Abstract_Value::class !== get_class() ) {
			$setters = parent::get_setters();
		}

		$properties = array_keys( get_object_vars( $this ) );

		foreach ( $properties as $property ) {
			$method_name = "set_{$property}_value";
			if ( method_exists( $this, $method_name ) ) {
				$setters[] = $method_name;
			}
		}

		/**
		 * Filter the value returned for get_setters() when implemented in a specific class name
		 *
		 * @since 4.14.9
		 *
		 * @param string[] $setters the list of setter methods returned
		 * @param Abstract_Value the object instance
		 *
		 * @return string[]
		 */
		$setters = apply_filters( "tec_tickets_commerce_{$this->get_value_type()}_value_get_setters", $setters, $this );

		/**
		 * Filter the value returned for get_setters() for all class names.
		 *
		 * @since 4.14.9
		 *
		 * @param string[] $setters the list of setter methods returned
		 * @param Abstract_Value the object instance
		 *
		 * @return string[]
		 */
		return apply_filters( 'tec_tickets_commerce_value_get_setters', $setters, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function update() {
		foreach ( $this->get_setters() as $setter ) {
			call_user_func( [ $this, $setter ] );
		}
	}
}