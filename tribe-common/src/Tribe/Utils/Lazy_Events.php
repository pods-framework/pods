<?php
/**
 * Provides methods for "lazy" objects to act upon life cycle events.
 *
 * @since   4.9.16
 *
 * @example
 * ```php
 *         class Lazy_List_Of_Stuff {
 *              use Tribe\Utils\Lazy_Events;
 *
 *              protected $list;
 *
 *              public function fetch_list(){
 *                  $cached = wp_cache_get( 'list_of_stuff_one' );
 *
 *                  if( false !== $cached ){
 *                      return $cached;
 *
 *                      if( null === $this->list ){
 *                          $this->list = really_expensive_calculation();
 *                      }
 *
 *                      $this->resolved();
 *                  }
 *
 *                  return $this->list;
 *              }
 *         }
 *
 *         class Lazy_Value {
 *              use Tribe\Utils\Lazy_Events;
 *
 *              protected $value;
 *
 *              public function calculate_value(){
 *                  $cached = wp_cache_get( 'expensive_value' );
 *
 *                  if( false !== $cached ){
 *                      return $cached;
 *
 *                      if( null === $this->value ){
 *                          $this->value = really_expensive_calculation();
 *                      }
 *
 *                      $this->resolved();
 *                  }
 *
 *                  return $this->value;
 *              }
 *          }
 *
 *          class List_And_Value {
 *              protected $list;
 *              protected $value;
 *
 *              public function __construct( Lazy_List_Of_Stuff $list, Lazy_Value $value ){
 *                  $this->list = $list;
 *                  $this->value = $value;
 *                  $this->list->on_resolve( [ $this, 'cache' ] );
 *                  $this->value->on_resolve( [ $this, 'cache' ] );
 *              }
 *
 *              public function cache(){
 *                  wp_cache_set( 'list_and_value', [
 *                      'list' => $this->list->fetch_list(),
 *                      'value' => $this->value->calculate_value(),
 *                  ]);
 *              }
 *
 *              public function get_list(){
 *                  $cached = wp_cache_get( 'list_and_value' );
 *
 *                  return $cached ? $cached['list'] : $this->list->fetch_list();
 *              }
 *
 *              public function get_value(){
 *                  $cached = wp_cache_get( 'list_and_value' );
 *
 *                  return $cached ? $cached['value'] : $this->value->fetch_value();
 *              }
 *          }
 *
 *
 *         $list = new Lazy_List_Of_Stuff();
 *         $value = new Lazy_Value();
 *         $list_and_value = new List_And_Value( $list, $value );
 *
 *         // Accessing `value` will make it so that `list` too will be cached.
 *         $list_and_value->get_value();
 * ````
 *
 * @package Tribe\Utils
 */

namespace Tribe\Utils;

/**
 * Trait Lazy_Events
 *
 * @since   4.9.16
 *
 * @package Tribe\Utils
 *
 * @property string $lazy_resolve_action The action to which the trait will hook to run the callback if the object
 *                                       resolved. Using classes should define the property if the default `shutdown`
 *                                       one is not correct.
 * @property int $lazy_resolve_priority The priority at which the resolution callback will be hooked on the
 *                                      `$lazy_resolve_action`; defaults to `10`.
 */
trait Lazy_Events {

	/**
	 * The callback that will be called when, and if, the lazy object resolved at least once.
	 *
	 * @since 4.9.16
	 *
	 * @var
	 */
	protected $lazy_resolve_callback;

	/**
	 * Sets the callback that will be hooked to the resolve action when, and if, the `resolved` method is called.
	 *
	 * @since 4.9.16
	 *
	 * @param callable $callback The callback that will be hooked on the `$lazy_resolve_action` (defaults to `shutdown`)
	 *                           if the `resolved` method is called.
	 *
	 * @return static The object instance.
	 *
	 * @see Lazy_Events::resolved()
	 */
	public function on_resolve( callable $callback = null ) {
		if ( null === $callback ) {
			return $this;
		}

		$this->lazy_resolve_callback = $callback;

		return $this;
	}

	/**
	 * Hooks the `$lazy_resolve_callback` to the `$lazy_resolve_action` with the `$lazy_resolve_priority` if set.
	 *
	 * @since 4.9.16
	 */
	protected function resolved() {
		if ( empty( $this->lazy_resolve_callback ) ) {
			return;
		}

		$action   = property_exists( $this, 'lazy_resolve_action' ) ?
			$this->lazy_resolve_action
			: 'shutdown';
		$priority = property_exists( $this, 'lazy_resolve_priority' ) ?
			$this->lazy_resolve_priority
			: 10;

		$hooked = has_action( $action, $this->lazy_resolve_callback );

		// Let's play it safe and move the resolution as late as possible.
		$new_priority = false !== $hooked ? max( $hooked, $priority ) : $priority;

		if ( is_numeric( $hooked ) && $hooked !== $new_priority ) {
			remove_action( $action, $this->lazy_resolve_callback, $hooked );
		}

		add_action( $action, $this->lazy_resolve_callback, $new_priority );
	}
}
