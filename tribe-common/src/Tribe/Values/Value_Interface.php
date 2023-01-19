<?php

namespace Tribe\Values;

interface Value_Interface {

	/**
	 * Creates a new instance of the class this method is called from.
	 *
	 * @since 4.14.9
	 *
	 * @return Abstract_Value
	 */
	public static function create();

	/**
	 * Returns the object type, to use in filters
	 *
	 * @since 4.14.9
	 *
	 * @return string
	 */
	public function get_value_type();

	/**
	 * Get the value initially passed when the object was instantiated
	 *
	 * @since 4.14.9
	 *
	 * @return mixed
	 */
	public function get_initial_representation();

	/**
	 * Get the current integer representation of the object value
	 *
	 * @since 4.14.9
	 *
	 * @return int
	 */
	public function get_integer();

	/**
	 * Get the current float representation of the object value
	 *
	 * @since 4.14.9
	 *
	 * @return float
	 */
	public function get_float();

	/**
	 * Get the current normalized value for the object
	 *
	 * @since 4.14.9
	 *
	 * @return float
	 */
	public function get_normalized_value();

	/**
	 * Get the current decimal precision set for the object
	 *
	 * @since 4.14.9
	 *
	 * @return int
	 */
	public function get_precision();

	/**
	 * Multiplies the value of the current object by the quantity supplied and return the result.
	 *
	 * @since 4.14.9
	 *
	 * @param int|float $multiplier the amount to multiply the object value by.
	 *
	 * @return float;
	 */
	public function multiply( $multiplier );

	/**
	 * Transforms any formatted numeric string into a numeric value
	 *
	 * @since 4.14.9
	 *
	 * @param int|float|string $value the formatted string.
	 *
	 * @return float
	 */
	public function normalize( $amount );

	/**
	 * Public setter to use for any object.
	 *
	 * Any time the value in a child class needs to be updated, use this method to do it, as it will update
	 * all properties of the object state.
	 *
	 * @since 4.14.9
	 *
	 * @param mixed $amount the value to set
	 */
	public function set_value( $amount );

	/**
	 * Public setter to use for any object.
	 *
	 * @since 4.14.14
	 *
	 * @param int $amount the precision value to set
	 */
	public function set_precision( $amount );

	/**
	 * Get all valid setters registered to this object instance, up the inheritance chain.
	 *
	 * Implemented in Tribe\Values\Value_Update.php
	 *
	 * @since 4.14.9
	 *
	 * @return string[]
	 */
	public function get_setters();

	/**
	 * Value loader. This method calls all registered setter methods in the
	 * inheritance chain every time the object is updated, so the values in each of the formats are always kept up to
	 * date.
	 *
	 * Implemented in Tribe\Values\Value_Update.php
	 *
	 * @since 4.14.9
	 */
	public function update();

	/**
	 * Adds the value of the current object to the sum of the values received and return the result.
	 *
	 * @since 4.14.9
	 *
	 * @param array $values an array of float and/or integer values to add.
	 *
	 * @return int|float
	 */
	public function sum( $values );
}