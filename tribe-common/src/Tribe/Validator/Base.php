<?php

/**
 * Class Tribe__Validator__Base
 *
 * Provides methods to validate values.
 */
class Tribe__Validator__Base implements Tribe__Validator__Interface {

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_not_null( $value ) {
		return null !== $value;
	}

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_null( $value ) {
		return null === $value;
	}

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_string( $value ) {
		return ! empty( $value ) && is_string( $value );
	}

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_string_or_empty( $value ) {
		if ( empty( $value ) ) {
			return true;
		}

		return $this->is_string( $value );
	}

	/**
	 * Whether the value is a timestamp or a string parseable by the strtotime function or not.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_time( $value ) {
		return is_numeric( $value ) || ( is_string( $value ) && strtotime( $value ) );
	}

	/**
	 * Whether the value corresponds to an existing user ID or not.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_user_id( $value ) {
		return is_numeric( $value ) && (bool) get_user_by( 'ID', $value );
	}

	/**
	 * Whether the value is a positive integer or not.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_positive_int( $value ) {
		return is_numeric( $value ) && (int) $value == $value && (int) $value > 0;
	}

	/**
	 * Whether the value is a list of positive integers only or not.
	 *
	 * @since 4.7.19
	 *
	 * @param     array|string|int $list
	 * @param string               $sep
	 *
	 * @return bool
	 */
	public function is_positive_int_list( $list, $sep = ',' ) {
		$sep  = is_string( $sep ) ? $sep : ',';
		$list = Tribe__Utils__Array::list_to_array( $list, $sep );

		$valid = array_filter( $list, [ $this, 'is_positive_int' ] );

		return ! empty( $valid ) && count( $valid ) === count( $list );
	}

	/**
	 * Trims a string.
	 *
	 * Differently from the trim method it will not use the second argument.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function trim( $value ) {
		return is_string( $value ) ? trim( $value ) : $value;
	}

	/**
	 * Whether the value(s) all map to existing post tags.
	 *
	 * @param mixed $tag
	 *
	 * @return bool
	 */
	public function is_post_tag( $tag ) {
		return $this->is_term_of_taxonomy( $tag, 'post_tag' );
	}

	/**
	 * Whether the term exists and is a term of the specified taxonomy.
	 *
	 * @param mixed  $term Either a single term `term_id` or `slug` or an array of
	 *                     `term_id`s and `slug`s
	 * @param string $taxonomy
	 *
	 * @return bool
	 */
	public function is_term_of_taxonomy( $term, $taxonomy ) {
		$terms = Tribe__Utils__Array::list_to_array( $term, ',' );

		if ( empty( $terms ) ) {
			return false;
		}

		foreach ( $terms as $t ) {
			if ( ! term_exists( $t, $taxonomy ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Whether the provided value points to an existing attachment ID or an existing image URL.
	 *
	 * @param int|string $image
	 *
	 * @return mixed
	 */
	public function is_image( $image ) {
		if ( $this->is_numeric( $image ) ) {
			return wp_attachment_is_image( $image );
		}

		if ( is_string( $image ) ) {
			$response = wp_remote_head( $image );

			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				return false;
			}

			$content_type = wp_remote_retrieve_header( $response, 'content-type' );

			if ( empty( $content_type ) || 0 !== strpos( $content_type, 'image' ) ) {
				return false;
			}

			$allowed_mime_types = get_allowed_mime_types();

			return ( in_array( $content_type, $allowed_mime_types ) );
		}

		return false;
	}

	/**
	 * Whether the provided value points to an existing attachment ID, an existing image URL, or is empty.
	 *
	 * @param int|string $image
	 *
	 * @return mixed
	 */
	public function is_image_or_empty( $image ) {
		if ( empty( $image ) ) {
			return true;
		}

		return $this->is_image( $image );
	}

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_numeric( $value ) {
		return is_numeric( $value );
	}

	/**
	 * Whether a string represents a valid array or not.
	 *
	 * Valid means that the string looks like a URL, not that the URL is online and reachable.
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public function is_url( $input ) {
		return (bool) filter_var( $input, FILTER_VALIDATE_URL );
	}

	/**
	 * Whether a string represents a valid array or not.
	 *
	 * Valid means that the string looks like a URL, not that the URL is online and reachable.
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public function is_url_or_empty( $input ) {
		if ( empty( $input ) ) {
			return true;
		}

		return $this->is_url( $input );
	}

	/**
	 * Whether a string represents a valid and registered post status or not.
	 *
	 * @param string $post_status
	 *
	 * @return bool
	 */
	public function is_post_status( $post_status ) {
		$post_stati = get_post_stati();
		if ( empty( $post_stati ) ) {
			return false;
		}

		return in_array( $post_status, $post_stati );
	}

	/**
	 * Converts a string, a CSV list to an array.
	 *
	 * @since 4.7.19
	 *
	 * @param string|array $list
	 *
	 * @return array
	 */
	public function list_to_array( $list ) {
		return Tribe__Utils__Array::list_to_array( $list );
	}
}
