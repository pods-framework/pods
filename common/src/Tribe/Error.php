<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Error {
	/**
	 * All the Errors Registered
	 * @var array
	 */
	private $items = array();

	/**
	 * Static Singleton Holder
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Setup all the hooks and filters
	 *
	 * @return void
	 */
	private function __construct() {
		$this->register( 'unknown', esc_html__( 'An Unknown error occurred' ) );
	}

	/**
	 * Make a quickly usable method to transform code/indexes to WP_Errors
	 *
	 * @see tribe_error()
	 *
	 * @param  string|array $indexes Which Error we are looking for
	 * @param  array        $context Gives the Error context
	 * @param  array        $sprintf Allows variables on the message
	 *
	 * @return WP_Error
	 */
	public function send( $indexes, $context = array(), $sprintf = array() ) {
		if ( ! $this->exists( $indexes ) ) {
			$indexes = array( 'unknown' );
		}

		// Fetches the Errors
		$messages = (array) $this->get( $indexes );
		$error    = new WP_Error;

		foreach ( $messages as $key => $message ) {
			// Allows variables when sending the message
			if ( ! empty( $sprintf ) ) {
				$message = vsprintf( $message, $sprintf );
			}
			// Add this Message to the WP_Error
			$error->add( $key, $message, $context );
		}

		return $error;
	}

	/**
	 * Register a new error based on a Namespace
	 *
	 * @param  string|array  $indexes  A list of the namespaces and last item should be the error name
	 * @param  string        $message  What is going to be the message associate with this indexes
	 *
	 * @return boolean
	 */
	public function register( $indexes, $message ) {
		if ( is_string( $indexes ) ) {
			// Each namespace should come with `:`
			$indexes = (array) explode( ':', $indexes );
		}

		// Couldn't register the error
		if ( empty( $indexes ) ) {
			return false;
		}

		$variable = &$this->items;
		$count    = count( $indexes );

		// Will create the Indexes based on the $slug
		foreach ( $indexes as $i => $index ) {
			if ( $count === $i + 1 ) {
				$variable[ $index ] = $message;
			} else {
				$variable = &$variable[ $index ];
			}
		}

		// Allows Chain Reactions
		return true;
	}

	/**
	 * Removes an error from the items
	 *
	 * @param  string|array  $indexes  A list of the namespaces and last item should be the error name
	 *
	 * @return boolean
	 */
	public function remove( $indexes ) {
		if ( ! $this->exists( $indexes ) ) {
			return false;
		}

		if ( is_string( $indexes ) ) {
			// Each namespace should come with `:`
			$indexes = (array) explode( ':', $indexes );
		}

		// Ensures that we don't modify the original
		$variable = &$this->items;
		$count    = count( $indexes );

		foreach ( $indexes as $i => $index ) {
			if ( $count === $i + 1 ) {
				unset( $variable[ $index ] );
			} else {
				$variable = &$variable[ $index ];
			}
		}

		return true;
	}

	/**
	 * Fetches the error or namespace
	 *
	 * @param  string|array  $indexes (optional)  A list of the namespaces and last item should be the error name
	 *
	 * @return null|array|string
	 */
	public function get( $indexes = null ) {
		if ( is_null( $indexes ) ) {
			return $this->items;
		}

		if ( is_string( $indexes ) ) {
			// Each namespace should come with `:`
			$indexes = (array) explode( ':', $indexes );
		}

		// Ensures that we don't modify the original
		$variable = $this->items;
		$count    = count( $indexes );

		foreach ( $indexes as $i => $index ) {
			if ( ! isset( $variable[ $index ] ) ) {
				// If we are on the last item and we don't have it set make it Null
				if ( $count === $i + 1 ) {
					return null;
				}
				continue;
			}

			$variable = $variable[ $index ];
		}

		$return = array();
		$was_namespace = is_array( $variable );

		/**
		 * @todo Allow fetching bigger groups
		 *       Right now you can only fetch the first group of messages
		 *       Trying to fetch Namespaces that contain other namespaces will bug
		 */
		foreach ( (array) $variable as $key => $value ) {
			$key = implode( ':', $indexes ) . ( $was_namespace ? ':' . $key : '' );
			$return[ $key ] = $value;
		}

		return $return;
	}

	/**
	 * Checks if a given error or namespace exists
	 *
	 * @param  string|array  $indexes  A list of the namespaces and last item should be the error name
	 *
	 * @return boolean
	 */
	public function exists( $indexes ) {
		$variable = $this->get( $indexes );
		return ! empty( $variable ) || is_array( $variable ) ? true : false;
	}
}
