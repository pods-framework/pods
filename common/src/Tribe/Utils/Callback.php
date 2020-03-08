<?php

class Tribe__Utils__Callback {

	/**
	 * Where we store all the Callbacks to allow removing of hooks
	 *
	 * @since  4.6.2
	 *
	 * @var array
	 */
	public $items = array();

	/**
	 * The Prefix we use for the Overloading replacement
	 *
	 * @since  4.6.2
	 *
	 * @var string
	 */
	protected $prefix = 'callback_';

	/**
	 * When used to wrap a Tribe callback this will be the slug or class to build.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * When used to wrap a Tribe callback this will be the method to call.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * Returns a callable for on this class that doesn't exist, but passes in the Key for Di52 Slug and it's method
	 * and arguments. It will relayed via overloading __call() on this same class.
	 *
	 * The lambda function suitable to use as a callback; when called the function will build the implementation
	 * bound to `$classOrInterface` and return the value of a call to `$method` method with the call arguments.
	 *
	 * @since  4.6.2
	 *
	 * @param string $slug                   A class or interface fully qualified name or a string slug.
	 * @param string $method                 The method that should be called on the resolved implementation with the
	 *                                       specified array arguments.
	 *
	 * @return array The callable
	 */
	public function get( $slug, $method ) {
		$container = Tribe__Container::init();
		$arguments = func_get_args();
		$is_empty = 2 === count( $arguments );

		// Remove Slug and Method
		array_shift( $arguments );
		array_shift( $arguments );

		$item = (object) array(
			'slug' => $slug,
			'method' => $method,
			'arguments' => $arguments,
			'is_empty' => $is_empty,
		);

		$key = md5( json_encode( $item ) );

		// Prevent this from been reset
		if ( isset( $this->items[ $key ] ) ) {
			return $this->items[ $key ];
		}

		$item->callback = $container->callback( $item->slug, $item->method );

		$this->items[ $key ] = $item;

		return array( $this, $this->prefix . $key );
	}

	/**
	 * Returns the Value passed as a simple Routing method for tribe_callback_return
	 *
	 * @since  4.6.2
	 *
	 * @param  mixed  $value  Value to be Routed
	 *
	 * @return mixed
	 */
	public function return_value( $value ) {
		return $value;
	}

	/**
	 * Calls the Lambda function provided by Di52 to allow passing of Params without having to create more
	 * methods into classes for simple callbacks that will only have a pre-determined value.
	 *
	 * @since  4.6.2
	 *
	 * @param string $slug                   A class or interface fully qualified name or a string slug.
	 * @param string $method                 The method that should be called on the resolved implementation with the
	 *                                       specified array arguments.
	 *
	 * @return mixed  The Return value used
	 */
	public function __call( $method, $args ) {
		$key = str_replace( $this->prefix, '', $method );

		if ( ! isset( $this->items[ $key ] ) ) {
			return false;
		}

		$item = $this->items[ $key ];

		// Allow for previous compatibility with tribe_callback
		if ( ! $item->is_empty ) {
			$args = $item->arguments;
		}

		return call_user_func_array( $item->callback, $args );
	}

	/**
	 * Tribe__Utils__Callback constructor.
	 *
	 * This is used to wrap a Tribe callable couple, a bound slug and method, to be used as a serializable callback.
	 *
	 * @since 4.9.5
	 *
	 * @param string $slug   The slug or class to call.
	 * @param string $method The method to call on the slug or class.
	 */
	public function __construct( $slug = null, $method = null ) {
		$this->slug   = $slug;
		$this->method = $method;
	}

	/**
	 * Returns the list of properties that should be serialized for the object.

	 *
	 * @since 4.9.5
	 *
	 * @return array An array of properties that should be serialized.
	 */
	public function __sleep() {
		return array( 'slug', 'method' );
	}

	/**
	 * Returns this callback slug or class.
	 *
	 * This only makes sense if this class is being used to wrap a Tribe callback couple (slug and method).
	 *
	 * @since 4.9.5
	 *
	 * @return string|null This Tribe callback wrapper slug or class.
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Returns this callback method.
	 *
	 * This only makes sense if this class is being used to wrap a Tribe callback couple (slug and method).
	 *
	 * @since 4.9.5
	 *
	 * @return string|null This Tribe callback method.
	 */
	public function get_method() {

		return $this->method;
	}
}
