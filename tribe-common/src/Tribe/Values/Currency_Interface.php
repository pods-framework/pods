<?php

namespace Tribe\Values;

interface Currency_Interface {

	/**
	 * Get the string representation of the Value object amount, with the currency symbol.
	 *
	 * @since 4.14.9
	 *
	 * @return string
	 */
	public function get_currency();

	/**
	 * Get the decimal representation of the Value object amount. Decimal is the float value, rounded to the precision.
	 *
	 * @since 4.14.9
	 *
	 * @return float
	 */
	public function get_decimal();

	/**
	 * Get the string representation of the Value object amount. String is formatted according to the Value configs.
	 *
	 * @since 4.14.9
	 *
	 * @return string
	 */
	public function get_string();

	/**
	 * Get the 3-character currency code to use.
	 *
	 * @since 4.14.9
	 *
	 * @return string
	 */
	public function get_currency_code();

	/**
	 * Get the character to be used as currency symbol.
	 *
	 * @since 4.14.9
	 *
	 * @return string
	 */
	public function get_currency_symbol();

	/**
	 * Get the position to use when setting the currency symbol. Positions can be prefix or postfix (suffix).
	 *
	 * @since 4.14.9
	 *
	 * @return string
	 */
	public function get_currency_symbol_position();

	/**
	 * Get the character to be used as decimal separator.
	 *
	 * @since 4.14.9
	 *
	 * @return string
	 */
	public function get_currency_separator_decimal();

	/**
	 * Get the character to be used as thousands separator.
	 *
	 * @since 4.14.9
	 *
	 * @return string
	 */
	public function get_currency_separator_thousands();

	/**
	 * Set up currency details for the currency implementation. This method must be implemented in the leaf class, the
	 * actual value class to be used in code.
	 *
	 * @since 4.14.9
	 */
	public function set_up_currency_details();
}