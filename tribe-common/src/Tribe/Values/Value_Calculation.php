<?php

namespace Tribe\Values;

trait Value_Calculation {

	/**
	 * Sets the current object with its value multiplied by $multiplier.
	 *
	 * @since 4.14.9
	 *
	 * @param int|float $multiplier the value to multiply by
	 *
	 * @return $this
	 */
	public function sub_total( $multiplier ) {
		$this->set_value( $this->multiply( $multiplier ) );

		return $this;
	}

	/**
	 * Sets the current object value to be the sum of its current value plus the values of all objects received in
	 * $values.
	 *
	 * @since 4.14.9
	 *
	 * @param Abstract_Value[] $values a list of Value objects
	 *
	 * @return $this
	 */
	public function total( $values ) {
		$num = array_map( function ( $obj ) {
			return $obj->get_float();
		}, $values );

		$this->set_value( $this->sum( $num ) );

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function sum( $values ) {
		$values[] = $this->get_float();

		return array_sum( $values );
	}

	/**
	 * @inheritDoc
	 */
	public function multiply( $multiplier ) {
		return $this->get_float() * $multiplier;
	}

	/**
	 * Rounds the current value to its precision and multiplies it by 10^precision to get an integer representation
	 * including decimals.
	 *
	 * @since 4.14.9
	 *
	 * @param int|float $value the value to transform
	 *
	 * @return int
	 */
	public function to_integer( $value ) {
		return (int) ( round( $value, $this->get_precision() ) * pow( 10, $this->get_precision() ) );
	}
}