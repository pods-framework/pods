<?php

namespace Pods;

/**
 * Integration abstract class.
 *
 * @since 2.8.0
 */
abstract class Integration {

	/**
	 * Integration hooks.
	 *
	 * @var array[] {
	 *     @type array $action {
	 *         @type callable $callback  The callback.
	 *         @type int      $priority  Priority.
	 *         @type int      $arguments Number of arguments.
	 *     }
	 *     @type array $filter {
	 *         @type callable $callback  The callback.
	 *         @type int      $priority  Priority.
	 *         @type int      $arguments Number of arguments.
	 *     }
	 * }
	 */
	protected $hooks = [
		'action' => [
			// 'name' => [ 'callback', 10, 2 ],
		],
		'filter' => [
			// 'name' => [ 'callback', 10, 2 ],
		],
	];

	/**
	 * Whether the integration is active.
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	public static function is_active() {
		return false;
	}

	/**
	 * Add the class hooks.
	 *
	 * @since 2.8.0
	 */
	public function hook() {
		foreach ( $this->hooks as $type => $hooks ) {
			foreach ( $hooks as $hook => $params ) {
				if ( is_string( $params[0] ) && is_callable( [ $this, $params[0] ] ) ) {
					$params[0] = [ $this, $params[0] ];
				}
				array_unshift( $params, $hook );
				call_user_func_array( 'add_' . $type, $params );
			}
		}
	}

	/**
	 * Remove the class hooks.
	 *
	 * @since 2.8.0
	 */
	public function unhook() {
		foreach ( $this->hooks as $type => $hooks ) {
			foreach ( $hooks as $hook => $params ) {
				if ( is_string( $params[0] ) && is_callable( $this, $params[0] ) ) {
					$params[0] = [ $this, $params[0] ];
				}
				array_unshift( $params, $hook );
				call_user_func_array( 'remove_' . $type, $params );
			}
		}
	}

}
