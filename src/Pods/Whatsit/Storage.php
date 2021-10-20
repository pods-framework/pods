<?php

namespace Pods\Whatsit;

use Pods\Whatsit;

/**
 * Storage class.
 *
 * @since 2.8.0
 */
abstract class Storage {

	/**
	 * @var string
	 */
	protected static $type = '';

	/**
	 * @var array
	 */
	protected $primary_args = [];

	/**
	 * @var array
	 */
	protected $secondary_args = [];

	/**
	 * @var bool
	 */
	protected $fallback_mode = true;

	/**
	 * Storage constructor.
	 */
	public function __construct() {
		// @todo Bueller?
	}

	/**
	 * Get the object storage type.
	 *
	 * @return string The object storage type.
	 */
	public function get_object_storage_type() {
		return static::$type;
	}

	/**
	 * Get object from storage.
	 *
	 * @return Whatsit|null
	 */
	public function get() {
		return null;
	}

	/**
	 * Find objects in storage.
	 *
	 * @param array $args Arguments to use.
	 *
	 * @return Whatsit[]
	 */
	public function find( array $args = [] ) {
		return [];
	}

	/**
	 * Setup arg with any potential variations.
	 *
	 * @param array  $args List of arguments.
	 * @param string $arg  Argument to setup.
	 *
	 * @return array List of arguments with arg values setup.
	 */
	public function setup_arg( array $args, $arg ) {
		if ( isset( $args[ $arg ] ) && $args[ $arg ] instanceof Whatsit ) {
			$args[ $arg . '_identifier' ] = (array) $args[ $arg ]->get_identifier();
			$args[ $arg . '_id' ]         = (array) $args[ $arg ]->get_id();
			$args[ $arg . '_name' ]       = (array) $args[ $arg ]->get_name();
			$args[ $arg ]                 = (array) $args[ $arg ]->get_name();
		}

		return $args;
	}

	/**
	 * Get arg value.
	 *
	 * @param array  $args List of arguments.
	 * @param string $arg  Argument to get values for.
	 *
	 * @return array|string|int|null Arg value(s).
	 */
	public function get_arg_value( $args, $arg ) {
		$arg_value = [];

		if ( array_key_exists( $arg, $args ) ) {
			$arg_value[] = is_array( $args[ $arg ] ) ? $args[ $arg ] : [ $args[ $arg ] ];
		}

		$secondary_variations = [
			'identifier',
			'id',
			'name',
		];

		foreach ( $secondary_variations as $variation ) {
			if ( ! array_key_exists( $arg . '_' . $variation, $args ) ) {
				continue;
			}

			$arg_value[] = is_array( $args[ $arg . '_' . $variation ] ) ? $args[ $arg . '_' . $variation ] : [ $args[ $arg . '_' . $variation ] ];
		}

		if ( empty( $arg_value ) ) {
			return '_null';
		}

		$arg_value = array_merge( ...$arg_value );
		$arg_value = array_unique( $arg_value );

		if ( 1 === count( $arg_value ) ) {
			$arg_value = current( $arg_value );
		}

		return $arg_value;
	}

	/**
	 * Add an object.
	 *
	 * @param Whatsit $object Object to add.
	 *
	 * @return string|int|false Object name, object ID, or false if not added.
	 */
	protected function add_object( Whatsit $object ) {
		return $this->save_object( $object );
	}

	/**
	 * Add an object.
	 *
	 * @param Whatsit $object Object to add.
	 *
	 * @return string|int|false Object name, object ID, or false if not added.
	 */
	public function add( Whatsit $object ) {
		/**
		 * Hook into the storage adding of an object.
		 *
		 * @param Whatsit          $object  Pod object.
		 * @param Storage $storage Storage object.
		 *
		 * @since 2.8.0
		 */
		do_action( 'pods_whatsit_storage_save', $object, $this );

		/**
		 * Hook into the storage adding of an object based on object type.
		 *
		 * @param Whatsit          $object  Pod object.
		 * @param Storage $storage Storage object.
		 *
		 * @since 2.8.0
		 */
		do_action( 'pods_whatsit_storage_save_' . $object->get_object_type(), $object, $this );

		$added = $this->add_object( $object );

		if ( $added && ! is_wp_error( $added ) ) {
			/**
			 * Hook into the storage adding of an object after adding.
			 *
			 * @param Whatsit          $object  Pod object.
			 * @param Storage $storage Storage object.
			 *
			 * @since 2.8.0
			 */
			do_action( 'pods_whatsit_storage_added', $object, $this );

			/**
			 * Hook into the storage adding of an object after adding based on object type.
			 *
			 * @param Whatsit          $object  Pod object.
			 * @param Storage $storage Storage object.
			 *
			 * @since 2.8.0
			 */
			do_action( 'pods_whatsit_storage_added_' . $object->get_object_type(), $object, $this );

			if ( true === $added ) {
				$added = (int) $object->get_id();

				if ( empty( $added ) ) {
					$added = $object->get_name();
				}
			}
		}//end if

		return $added;
	}

	/**
	 * Save an object.
	 *
	 * @param Whatsit $object Object to save.
	 *
	 * @return string|int|false Object name, object ID, or false if not saved.
	 */
	protected function save_object( Whatsit $object ) {
		return false;
	}

	/**
	 * Save an object.
	 *
	 * @param Whatsit $object Object to save.
	 *
	 * @return string|int|false Object name, object ID, or false if not saved.
	 */
	public function save( Whatsit $object ) {
		/**
		 * Hook into the storage saving of an object.
		 *
		 * @param Whatsit          $object  Pod object.
		 * @param Storage $storage Storage object.
		 *
		 * @since 2.8.0
		 */
		do_action( 'pods_whatsit_storage_save', $object, $this );

		/**
		 * Hook into the storage saving of an object based on object type.
		 *
		 * @param Whatsit          $object  Pod object.
		 * @param Storage $storage Storage object.
		 *
		 * @since 2.8.0
		 */
		do_action( 'pods_whatsit_storage_save_' . $object->get_object_type(), $object, $this );

		$saved = $this->save_object( $object );

		if ( $saved && ! is_wp_error( $saved ) ) {
			/**
			 * Hook into the storage saving of an object after saving.
			 *
			 * @param Whatsit          $object  Pod object.
			 * @param Storage $storage Storage object.
			 *
			 * @since 2.8.0
			 */
			do_action( 'pods_whatsit_storage_saved', $object, $this );

			/**
			 * Hook into the storage saving of an object after saving based on object type.
			 *
			 * @param Whatsit          $object  Pod object.
			 * @param Storage $storage Storage object.
			 *
			 * @since 2.8.0
			 */
			do_action( 'pods_whatsit_storage_saved_' . $object->get_object_type(), $object, $this );

			if ( true === $saved ) {
				$saved = (int) $object->get_id();

				if ( empty( $saved ) ) {
					$saved = $object->get_name();
				}
			}
		}//end if

		return $saved;
	}

	/**
	 * Duplicate an object.
	 *
	 * @param Whatsit $object Object to duplicate.
	 *
	 * @return string|int|false Duplicated object name, duplicated object ID, or false if not duplicated.
	 */
	public function duplicate( Whatsit $object ) {
		$duplicated_object = clone $object;

		$duplicated_object->set_arg( 'id', null );
		$duplicated_object->set_arg( 'name', $duplicated_object->get_name() . '_copy' );

		$added = $this->add( $duplicated_object );

		if ( $added && ! is_wp_error( $added ) ) {
			/**
			 * Hook into the storage duplication of an object after duplication.
			 *
			 * @param Whatsit          $object  Original pod object.
			 * @param Storage $storage Storage object.
			 *
			 * @since 2.8.0
			 */
			do_action( 'pods_whatsit_storage_duplicated', $object, $this );

			/**
			 * Hook into the storage duplication of an object after duplication based on object type.
			 *
			 * @param Whatsit          $object  Original pod object.
			 * @param Storage $storage Storage object.
			 *
			 * @since 2.8.0
			 */
			do_action( 'pods_whatsit_storage_duplicated_' . $object->get_object_type(), $object, $this );
		}//end if

		return $added;
	}

	/**
	 * Delete an object.
	 *
	 * @param Whatsit $object Object to delete.
	 *
	 * @return bool
	 */
	protected function delete_object( Whatsit $object ) {
		return false;
	}

	/**
	 * Delete an object.
	 *
	 * @param Whatsit $object Object to delete.
	 *
	 * @return bool
	 */
	public function delete( Whatsit $object ) {
		/**
		 * Hook into the storage deletion of an object.
		 *
		 * @param Whatsit          $object  Pod object.
		 * @param Storage $storage Storage object.
		 *
		 * @since 2.8.0
		 */
		do_action( 'pods_whatsit_storage_delete', $object, $this );

		/**
		 * Hook into the storage deletion of an object based on object type.
		 *
		 * @param Whatsit          $object  Pod object.
		 * @param Storage $storage Storage object.
		 *
		 * @since 2.8.0
		 */
		do_action( 'pods_whatsit_storage_delete_' . $object->get_object_type(), $object, $this );

		$deleted = $this->delete_object( $object );

		if ( $deleted ) {
			/**
			 * Hook into the storage deletion of an object after deletion based on object type.
			 *
			 * @param Whatsit          $object  Pod object.
			 * @param Storage $storage Storage object.
			 *
			 * @since 2.8.0
			 */
			do_action( 'pods_whatsit_storage_deleted', $object, $this );

			/**
			 * Hook into the storage deletion of an object after deletion based on object type.
			 *
			 * @param Whatsit          $object  Pod object.
			 * @param Storage $storage Storage object.
			 *
			 * @since 2.8.0
			 */
			do_action( 'pods_whatsit_storage_deleted_' . $object->get_object_type(), $object, $this );
		}//end if

		return $deleted;
	}

	/**
	 * Reset an object's item data.
	 *
	 * @param Whatsit $object Object of items to reset.
	 *
	 * @return bool
	 */
	public function reset( Whatsit $object ) {
		return false;
	}

	/**
	 * Get object argument data.
	 *
	 * @param Whatsit $object Object with arguments to save.
	 *
	 * @return array
	 */
	public function get_args( Whatsit $object ) {
		return $object->get_args();
	}

	/**
	 * Save object argument data.
	 *
	 * @param Whatsit $object Object with arguments to save.
	 *
	 * @return bool
	 */
	public function save_args( Whatsit $object ) {
		return false;
	}

	/**
	 * Whether to enable fallback mode for falling back to parent storage options.
	 *
	 * @param bool $enabled Whether to enable fallback mode.
	 */
	public function fallback_mode( $enabled = true ) {
		$this->fallback_mode = (boolean) $enabled;
	}

}
