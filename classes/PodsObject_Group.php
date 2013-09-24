<?php
/**
 * @package Pods
 *
 * Class PodsObject_Group
 */
class PodsObject_Group extends PodsObject {

	/**
	 * Post type / meta key prefix for internal values
	 *
	 * @var string
	 */
	protected $_post_type = '_pods_group';

	/**
	 * Deprecated keys / options
	 *
	 * @var array
	 */
	protected $_deprecated_keys = array(
		'ID' => 'id',
		'post_title' => 'label',
		'post_name' => 'name',
		'post_content' => 'description',
		'post_parent' => 'parent_id'
	);

	/**
	 * Method names for accessing internal keys
	 *
	 * @var array
	 */
	protected $_methods = array(
		'fields'
	);

	/**
	 * Return field array from Object, a field's data, or a field option
	 *
	 * @param string|null $field Object Field name
	 * @param string|null $option Field option
	 *
	 * @return array|mixed
	 *
	 * @since 2.3.10
	 */
	public function fields( $field = null, $option = null ) {

		if ( !isset( $this->_object[ 'fields' ] ) ) {
			if ( $this->is_custom() ) {
				if ( isset( $this->_object[ '_fields' ] ) && !empty( $this->_object[ '_fields' ] ) ) {
					foreach ( $this->_object[ '_fields' ] as $field ) {
						$field = pods_object_field( $field, 0, $this->_live, $this->_object[ 'id' ] );

						if ( $field->is_valid() ) {
							$this->_object[ 'fields' ][ $field[ 'name' ] ] = $field;
						}
					}
				}
			}
			else {
				$find_args = array(
					'post_type' => '_pods_field',
					'posts_per_page' => -1,
					'nopaging' => true,
					'post_parent' => $this->_object[ 'parent_id' ],
					'orderby' => 'menu_order',
					'order' => 'ASC'
				);

				$fields = get_posts( $find_args );

				$this->_object[ 'fields' ] = array();

				if ( !empty( $fields ) ) {
					foreach ( $fields as $field ) {
						$field = pods_object_field( $field, 0, $this->_live, $this->_object[ 'id' ] );

						if ( $field->is_valid() ) {
							$this->_object[ 'fields' ][ $field[ 'name' ] ] = $field;
						}
					}
				}
			}
		}

		return $this->_fields( 'fields', $field, $option );

	}

    /**
     * Save a Object by giving an array of option data or set a specific option to a specific value.
     *
     * @param array|string $options Either an associative array of option information or a option name
     * @param mixed $value (optional) Value of the option, if $data is a option name
	 * @param bool $refresh (optional) Refresh the current object
     *
     * @return int|bool The Object ID or false if failed
     *
     * @since 2.3.10
	 */
	public function save( $options = null, $value = null, $refresh = true ) {

		if ( !$this->is_valid() ) {
			return false;
		}

		if ( null !== $value || !is_array( $options ) ) {
			$options = array(
				$options => $value
			);
		}

		if ( empty( $options ) ) {
			return $this->_object[ 'id' ];
		}

		$params = $options;

		$params[ 'id' ] = $this->_object[ 'id' ];

		// For use later in actions
		$_object = $this->_object;

		$params = apply_filters( 'pods_object_pre_save_' . $this->_action_type, $params, $_object );

		// @todo Save group, then fields to group and Pod
		$id = $params[ 'id' ];

		// Refresh object
		if ( $refresh ) {
			$id = $this->load( null, $id );
		}
		// Just update options
		else {
			foreach ( $params as $option => $value ) {
				if ( 'id' != $option ) {
					$this->offsetSet( $option, $value );
				}
			}
		}

		if ( 0 < $id ) {
			$this->_action( 'pods_object_save', $_object[ 'id' ], $_object[ 'name' ], $_object[ 'parent' ], $_object );
		}

		return $id;

	}

    /**
     * Duplicate a Object, optionally giving an array of option data or set a specific option to a specific value.
     *
     * @param array|string $options (optional) Either an associative array of option information or a option name
     * @param mixed $value (optional) Value of the option, if $data is a option name
	 * @param bool $replace (optional) Replace the current object
     *
     * @return int|bool The new Object ID or false if failed
     *
     * @since 2.3.10
	 */
	public function duplicate( $options = null, $value = null, $replace = false ) {

		if ( !$this->is_valid() ) {
			return false;
		}

		if ( null !== $value && !is_array( $options ) ) {
			$options = array(
				$options => $value
			);
		}

		if ( empty( $options ) ) {
			return $this->_object[ 'id' ];
		}

		$params = $options;

		$params[ 'id' ] = $this->_object[ 'id' ];
		$params[ 'name' ] = $this->_object[ 'name' ];

		// For use later in actions
		$_object = $this->_object;

		$params = apply_filters( 'pods_object_pre_duplicate_' . $this->_action_type, $params, $_object );

		// @todo Duplicate group, and then the fields in group
		$id = $params[ 'id' ];

		if ( $replace ) {
			// Replace object
			$id = $this->load( null, $id );
		}

		if ( 0 < $id ) {
			$this->_action( 'pods_object_duplicate', $id, $_object[ 'id' ], $_object[ 'name' ], $_object[ 'parent' ], $_object );
		}

		return $id;

	}

    /**
     * Delete the Object
     *
     * @return bool Whether the Object was successfully deleted
     *
     * @since 2.3.10
     */
	public function delete() {

		if ( !$this->is_valid() ) {
			return false;
		}

		$params = array(
			'id' => $this->_object[ 'id' ],
			'name' => $this->_object[ 'name' ]
		);

		// For use later in actions
		$_object = $this->_object;

		$params = apply_filters( 'pods_object_pre_delete_' . $this->_action_type, $params, $_object );

		$success = false;

		if ( 0 < $params[ 'id' ] ) {
			// @todo Delete fields in group and then the group itself
			$success = true;
		}

		if ( $success ) {
			$this->_action( 'pods_object_delete', $_object[ 'id' ], $_object[ 'name' ], $_object[ 'parent' ], $_object );
		}

		// Can't destroy object, so let's destroy the data and invalidate the object
		$this->destroy();

		return $success;

	}
}