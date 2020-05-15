<?php

namespace Pods\Whatsit;

use Pods\Whatsit;

/**
 * Storage class.
 *
 * @since 2.8
 */
abstract class Storage {

	/**
	 * @var string
	 */
	protected static $type = '';

	/**
	 * @var array
	 */
	protected $primary_args = array();

	/**
	 * @var array
	 */
	protected $secondary_args = array();

	/**
	 * Storage constructor.
	 */
	public function __construct() {
		// @todo Bueller?
	}

	/**
	 * Get storage type.
	 *
	 * @return string
	 */
	public function get_storage_type() {
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
	public function find( array $args = array() ) {
		return array();
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
		 * @since 2.8
		 */
		do_action( 'pods_whatsit_storage_save', $object, $this );

		/**
		 * Hook into the storage adding of an object based on object type.
		 *
		 * @param Whatsit          $object  Pod object.
		 * @param Storage $storage Storage object.
		 *
		 * @since 2.8
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
			 * @since 2.8
			 */
			do_action( 'pods_whatsit_storage_added', $object, $this );

			/**
			 * Hook into the storage adding of an object after adding based on object type.
			 *
			 * @param Whatsit          $object  Pod object.
			 * @param Storage $storage Storage object.
			 *
			 * @since 2.8
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
		 * @since 2.8
		 */
		do_action( 'pods_whatsit_storage_save', $object, $this );

		/**
		 * Hook into the storage saving of an object based on object type.
		 *
		 * @param Whatsit          $object  Pod object.
		 * @param Storage $storage Storage object.
		 *
		 * @since 2.8
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
			 * @since 2.8
			 */
			do_action( 'pods_whatsit_storage_saved', $object, $this );

			/**
			 * Hook into the storage saving of an object after saving based on object type.
			 *
			 * @param Whatsit          $object  Pod object.
			 * @param Storage $storage Storage object.
			 *
			 * @since 2.8
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
			 * @since 2.8
			 */
			do_action( 'pods_whatsit_storage_duplicated', $object, $this );

			/**
			 * Hook into the storage duplication of an object after duplication based on object type.
			 *
			 * @param Whatsit          $object  Original pod object.
			 * @param Storage $storage Storage object.
			 *
			 * @since 2.8
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
		 * @since 2.8
		 */
		do_action( 'pods_whatsit_storage_delete', $object, $this );

		/**
		 * Hook into the storage deletion of an object based on object type.
		 *
		 * @param Whatsit          $object  Pod object.
		 * @param Storage $storage Storage object.
		 *
		 * @since 2.8
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
			 * @since 2.8
			 */
			do_action( 'pods_whatsit_storage_deleted', $object, $this );

			/**
			 * Hook into the storage deletion of an object after deletion based on object type.
			 *
			 * @param Whatsit          $object  Pod object.
			 * @param Storage $storage Storage object.
			 *
			 * @since 2.8
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

}
