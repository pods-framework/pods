<?php
/**
 * Class used to manage and add compatibility classes via a queue across our plugins.
 *
 * @since 4.14.0
 */
namespace Tribe\Utils;

use Tribe\Utils\Element_Classes;

/**
 * Compatibility_Classes class
 *
 * @since 4.14.0
 */
class Compatibility_Classes {
	/**
	 * Stores all the classes.
	 * In the format: ['class' => true, 'class => false ]
	 *
	 * @var array<string,bool>
	 */
	protected $classes = [];

	/**
	 * Stores all the admin classes.
	 * In the format: ['class' => true, 'class => false ]
	 *
	 * @var array<string,bool>
	 */
	protected $admin_classes = [];

	/**
	 * Queue-aware method to get the classes array.
	 * Returns the array of classes to add.
	 *
	 * @since 4.14.0
	 *
	 * @param string $queue The queue we want to get 'admin', 'display', 'all'.
	 * @return array<string,bool> A map of the classes for the queue.
	 */
	public function get_classes( $queue = 'display' ) {
		switch( $queue ) {
			case 'admin':
				return $this->admin_classes;
				break;
			case 'all':
				return array_merge( $this->classes, $this->admin_classes );
				break;
			default:
				return $this->classes;
				break;
		}
	}

	/**
	 * Returns the array of classnames to add
	 *
	 * @since 4.14.0
	 *
	 * @param string $queue The queue we want to get 'admin', 'display', 'all'.
	 * @return array<string> The list of class names.
	 */
	public function get_class_names( $queue = 'display' ) {
		$classes = $this->get_classes( $queue );

		return array_keys(
			array_filter(
				$classes,
				static function( $v ) {
					return $v;
				},
				ARRAY_FILTER_USE_KEY
			)
		);
	}

	/**
	 * Checks if a class is in the queue,
	 * wether it's going to be added or not.
	 *
	 * @since 4.14.0
	 *
	 * @param string $class The class we are checking for.
	 * @param string $queue The queue we want to check 'admin', 'display', 'all'
	 * @return boolean Whether a class exists or not in the queue.
	 */
	public function class_in_queue( $class, $queue = 'display' ) {
		$classes = $this->get_classes( $queue );

		return array_key_exists( $class, $classes );
	}

	/**
	 * Checks if a class is in the queue and going to be added.
	 *
	 * @since 4.14.0
	 *
	 * @param string $class The class we are checking for.
	 * @param string $queue The queue we want to check 'admin', 'display', 'all'
	 * @return boolean Whether a class is currently queued or not.
	 */
	public function class_is_enqueued( $class, $queue = 'display' ) {
		$classes = $this->get_classes( $queue );
		if ( ! $this->class_in_queue( $class, $queue ) ) {
			return false;
		}

		return $classes[ $class ];
	}

	/**
	 * Dequeues a class.
	 *
	 * @since 4.14.0
	 *
	 * @param string $class
	 * @param string $queue The queue we want to alter 'admin', 'display', 'all'
	 * @return boolean
	 */
	public function dequeue_class( $class, $queue = 'display' ) {
		if ( ! $this->class_in_queue( $class, $queue ) ) {
			return false;
		}

		if ( 'admin' !== $queue ) {
			$this->classes[ $class ] = false;
		}

		if ( 'display' !== $queue ) {
			$this->admin_classes[ $class ] = false;
		}

		return true;

	}

	/**
	 * Enqueues a class.
	 *
	 * @since 4.14.0
	 *
	 * @param string $class
	 * @param string $queue The queue we want to alter 'admin', 'display', 'all'
	 * @return false
	 */
	public function enqueue_class( $class, $queue = 'display' ) {
		if ( ! $this->class_in_queue( $class, $queue ) ) {
			return false;
		}

		if ( 'admin' !== $queue ) {
			$this->classes[ $class ] = true;
			return true;
		}

		if ( 'display' !== $queue ) {
			$this->admin_classes[ $class ] = true;
			return true;
		}

		// Something went wrong.
		return false;
	}

	/**
	 * Add a single class to the queue.
	 *
	 * @since 4.14.0
	 *
	 * @param string $class The class to add.
	 * @param string $queue The queue we want to alter 'admin', 'display', 'all'
	 * @return void
	 */
	public function add_class( $class, $queue = 'display' ) {
		if ( empty( $class ) ) {
			return;
		}

		if ( is_array( $class ) ) {
			$this->add_classes( $class, $queue );
		} elseif ( $this->should_add_compatibility_class_to_queue( $class, $queue ) ) {

			$class = sanitize_html_class( $class );

			if ( 'admin' !== $queue ) {
				$this->classes[ $class ] = true ;
			}

			if ( 'display' !== $queue ) {
				$this->admin_classes[ $class ] = true ;
			}
		}
	}

	/**
	 * Add an array of classes to the queue.
	 *
	 * @since 4.14.0
	 *
	 * @param array<string,boolean> $class The classes to add.
	 * @return void
	 */
	public function add_classes( array $classes, $queue = 'display' ) {
		foreach ( $classes as $key => $value ) {
			// If the classes are passed as class => bool, only add ones set to true.
			if ( is_bool( $value ) && false !== $value  ) {
				$this->add_class( $key, $queue );
			} else {
				$this->add_class( $value, $queue );
			}
		}
	}

	/**
	 * Remove a single class from the queue.
	 *
	 * @since 4.14.0
	 *
	 * @param string $class The class to remove.
	 * @return void
	 */
	public function remove_class( $class, $queue = 'display' ) {
		if ( 'admin' !== $queue ) {
			$this->classes = array_filter(
				$this->classes,
				static function( $k ) use ( $class ) {
					return $k !== $class;
				},
				ARRAY_FILTER_USE_KEY
			);
		}

		if ( 'display' !== $queue ) {
			$this->admin_classes = array_filter(
				$this->admin_classes,
				static function( $k ) use ( $class ) {
					return $k !== $class;
				},
				ARRAY_FILTER_USE_KEY
			);
		}
	}

	/**
	 * Remove an array of classes from the queue.
	 *
	 * @since 4.14.0
	 *
	 * @param array<string> $classes The classes to remove.
	 * @return void
	 */
	public function remove_classes( array $classes, $queue = 'display' ) {
		if ( empty( $classes ) || ! is_array( $classes) ) {
			return;
		}

		foreach ( $classes as $class ) {
			$this->remove_class( $class, $queue );
		}
	}

	/**
	 * Adds the enqueued classes to the compatibility class array.
	 *
	 * @since 4.14.0
	 *
	 * @param array<string> $classes An array of compatibility class names.
	 * @return array Array of compatibility classes.
	 */
	public function add_compatibility_classes( $classes = [] ) {
		// Make sure they should be added.
		if( ! $this->should_add_compatibility_classes( $this->get_class_names(), (array) $classes, 'display' ) ) {
			return $classes;
		}

		$element_classes = new Element_Classes( $this->get_class_names() );

		return array_merge( $classes, $element_classes->get_classes() );
	}

	/**
	 * Adds the enqueued classes to the compatibility class array.
	 *
	 * @since 4.14.0
	 *
	 * @param string $classes The existing compatibility class names.
	 *
	 * @return string String of admin compatibility classes.
	 */
	public function add_admin_compatibility_classes( $classes ) {
		$existing_classes = explode( ' ', $classes );
		// Make sure they should be added.
		if ( ! $this->should_add_compatibility_classes( $this->get_class_names( 'admin' ), (array) $existing_classes, 'admin' ) ) {
			// Ensure we return the current string on false!
			return $classes;
		}

		$element_classes = new Element_Classes( array_merge( $existing_classes, $this->get_class_names( 'admin' ) ) );

		return $element_classes->get_classes_as_string();

	}

	/**
	 * Should a individual class be added to the queue.
	 *
	 * @since 4.14.0
	 *
	 * @param string $class The compatibility class we wish to add.
	 *
	 * @return boolean Whether to add tribe compatibility classes to the queue.
	 */
	private function should_add_compatibility_class_to_queue( $class, $queue = 'display' ) {
		/**
		 * Filter whether to add the compatibility class to the queue or not.
		 *
		 * @since 4.14.0
		 *
		 * @param boolean $add Whether to add the class to the queue or not.
		 * @param array   $class The array of compatibility class names to add.
		 * @param string  $queue The queue we want to get 'admin', 'display', 'all'.
		 */
		return (bool) apply_filters( 'tribe_compatibility_class_should_add_to_queue', false, $class, $queue );
	}

	/**
	 * Logic for whether the compatibility classes, as a whole, should be added.
	 *
	 * @since 4.14.0
	 *
	 * @param array $add_classes      An array of compatibility class names to add.
	 * @param array $existing_classes An array of existing compatibility class names from WP.
	 * @param string $queue The queue we want to get 'admin', 'display', 'all'.
	 *
	 * @return boolean Whether to add tribe compatibility classes.
	 */
	private function should_add_compatibility_classes( array $add_classes, array $existing_classes, $queue ) {
		/**
		 * Filter whether to add tribe compatibility classes or not.
		 *
		 * @since 4.14.0
		 *
		 * @param boolean $add              Whether to add classes or not.
		 * @param array   $add_classes      The array of compatibility class names to add.
		 * @param array   $existing_classes An array of existing compatibility class names from WP.
		 * @param string  $queue            The queue we want to get 'admin', 'display', 'all'.
		 *
		 */
		return (bool)apply_filters( 'tribe_compatibility_classes_should_add', false, $queue, $add_classes, $existing_classes );
	}
}
