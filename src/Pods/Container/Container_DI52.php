<?php

namespace Pods\Container;

use Pods\Prefixed\lucatume\DI52\Container as DI52Container;

/**
 * Describes the interface of a container that exposes methods to read its entries.
 *
 * @credit The StellarWP team - https://github.com/stellarwp/container-contract
 *
 * @since 3.0
 *
 * @method mixed getVar( string $key, mixed|null $default = null )
 * @method void register( string $serviceProviderClass, string ...$alias )
 * @method self when( string $class )
 * @method self needs( string $id )
 * @method void give( mixed $implementation )
 */
class Container_DI52 implements Container_Interface {

	/**
	 * @var self
	 */
	protected static $instance;

	/**
	 * @var DI52Container
	 */
	protected $container;

	/**
	 * Container constructor.
	 *
	 * @param object $container The container to use.
	 */
	public function __construct( $container = null ) {
		$this->container = $container ?: new DI52Container();
	}

	/**
	 * @return self
	 */
	public static function init() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @inheritDoc
	 */
	public function bind( string $id, $implementation = null, array $afterBuildMethods = null ) {
		$this->container->bind( $id, $implementation, $afterBuildMethods );
	}

	/**
	 * @inheritDoc
	 */
	public function get( string $id ) {
		return $this->container->get( $id );
	}

	/**
	 * @return DI52Container
	 */
	public function get_container() {
		return $this->container;
	}

	/**
	 * @inheritDoc
	 */
	public function has( string $id ) {
		return $this->container->has( $id );
	}

	/**
	 * @inheritDoc
	 */
	public function singleton( string $id, $implementation = null, array $afterBuildMethods = null ) {
		$this->container->singleton( $id, $implementation, $afterBuildMethods );
	}

	/**
	 * Defer all other calls to the container object.
	 */
	#[\ReturnTypeWillChange]
	public function __call( $name, $arguments ) {
		return $this->container->{$name}( ...$arguments );
	}
}
