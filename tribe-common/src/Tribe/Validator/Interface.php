<?php

/**
 * Interface Tribe__Validator__Interface
 *
 * Models any class that provides methods to validate values.
 */
interface Tribe__Validator__Interface {
	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_numeric( $value );

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_string( $value );

	/**
	 * Whether the value is a timestamp or a string parseable by the strtotime function or not.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_time( $value );

	/**
	 * Whether the value corresponds to an existing user ID or not.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_user_id( $value );

	/**
	 * Whether the value is a positive integer or not.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_positive_int( $value );

	/**
	 * Trims a string.
	 *
	 * Differently from the trim method it will not use the second argument.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function trim( $value );

	/**
	 * Whether the value(s) all map to existing post tags.
	 *
	 * @param mixed $tag
	 *
	 * @return bool
	 */
	public function is_post_tag( $tag );

	/**
	 * Whether the term exists and is a term of the specified taxonomy.
	 *
	 * @param mixed  $term Either a single term `term_id` or `slug` or an array of
	 *                     `term_id`s and `slug`s
	 * @param string $taxonomy
	 *
	 * @return bool
	 */
	public function is_term_of_taxonomy( $term, $taxonomy );

	/**
	 * Whether the provided value points to an existing attachment ID or an existing image URL.
	 *
	 * @param int|string $image
	 *
	 * @return mixed
	 */
	public function is_image( $image );

	/**
	 * Whether a list or array of only contains positive integers or not.
	 *
	 * @since 4.7.19
	 *
	 * @param  string|array $list
	 * @param string $sep The separator used in the list to separate the elements; ignored if
	 *                                  the input value is an array.
	 *
	 * @return bool
	 */
	public function is_positive_int_list( $list, $sep = ',' );
}
