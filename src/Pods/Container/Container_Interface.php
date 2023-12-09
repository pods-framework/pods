<?php

namespace Pods\Container;

/**
 * Describes the interface of a container that exposes methods to read its entries.
 *
 * @credit The StellarWP team - https://github.com/stellarwp/container-contract
 *
 * @since 3.0
 */
interface Container_Interface {
	/**
	 * Binds an interface, a class or a string slug to an implementation.
	 *
	 * Existing implementations are replaced.
	 *
	 * @since 3.0
	 *
	 * @param string|class-string $id             Identifier of the entry to look for.
	 * @param mixed               $implementation The implementation that should be bound to the alias(es); can be a
	 *                                            class name, an object or a closure.
	 *
	 * @return void
	 */
	public function bind( string $id, $implementation = null );

	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * @since 3.0
	 *
	 * @template T
	 *
	 * @param string|class-string<T> $id Identifier of the entry to look for.
	 *
	 * @return ($id is class-string<T> ? T : mixed) Entry.
	 */
	public function get( string $id );

	/**
	 * Returns true if the container can return an entry for the given identifier.
	 * Returns false otherwise.
	 *
	 * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
	 * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
	 *
	 * @since 3.0
	 *
	 * @param string|class-string $id Identifier of the entry to look for.
	 *
	 * @return bool
	 */
	public function has( string $id );

	/**
	 * Binds an interface a class or a string slug to an implementation and will always return the same instance.
	 *
	 * @since 3.0
	 *
	 * @param string|class-string $id             Identifier of the entry to look for.
	 * @param mixed               $implementation The implementation that should be bound to the alias(es); can be a
	 *                                            class name, an object or a closure.
	 *
	 * @return void This method does not return any value.
	 */
	public function singleton( string $id, $implementation = null );
}
